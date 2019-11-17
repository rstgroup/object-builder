<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Builder;

use PhpParser\Lexer\Emulative;
use PhpParser\Node\Stmt;
use PhpParser\ParserFactory;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use RstGroup\ObjectBuilder\Builder;
use RstGroup\ObjectBuilder\BuildingError;
use Throwable;

final class Reflection implements Builder
{
    /** @var ParameterNameStrategy */
    private $parameterNameStrategy;

    /** @codeCoverageIgnore */
    public function __construct(ParameterNameStrategy $parameterNameStrategy)
    {
        $this->parameterNameStrategy = $parameterNameStrategy;
    }

    /**
     * @param mixed[] $data
     * @throws BuildingError
     */
    public function build(string $class, array $data): object
    {
        try {
            $classReflection = new ReflectionClass($class);

            $constructor = $classReflection->getConstructor();
            $parameters = [];

            if (null !== $constructor) {
                /** @var \Traversable $iterator */
                $iterator = $this->collect($constructor, $data);
                $parameters = iterator_to_array($iterator);
            }

            return new $class(...$parameters);
        } catch (Throwable $exception) {
            throw new BuildingError('Cant build object', 0, $exception);
        }
    }

    /**
     * @param mixed[] $data
     * @return mixed[]
     */
    private function collect(ReflectionMethod $constructor, array $data): iterable
    {
        foreach ($constructor->getParameters() as $parameter) {
            $name = $parameter->getName();

            if ($this->parameterDataIsInData($name, $data)) {
                $parsedData = [];

                foreach ($data as $key => $value) {
                    $parsedData[$this->parameterNameStrategy->getName($key)] = $value;
                }

                yield $this->buildParameter($parameter, $parsedData[$name], $constructor);
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                yield $parameter->getDefaultValue();
                continue;
            }

            if ($parameter->allowsNull()) {
                yield null;
            }
        }
    }

    /** @param mixed[] $data */
    private function parameterDataIsInData(string $parameterName, array $data): bool
    {
        foreach (array_keys($data) as $key) {
            if ($parameterName === $this->parameterNameStrategy->getName($key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    private function buildParameter(ReflectionParameter $parameter, $data, ReflectionMethod $constructor)
    {
        $class = $parameter->getClass();

        if (null !== $class) {
            return $this->build($class->getName(), $data);
        }

        if ($parameter->isArray()) {
            $parser = new PhpDocParser(new TypeParser(), new ConstExprParser());
            /** @var string $comment */
            $comment = $constructor->getDocComment();
            $node = $parser->parse(new TokenIterator((new Lexer())->tokenize($comment)));
            foreach ($node->getParamTagValues() as $node) {
                if ($node->parameterName === '$' . $parameter->getName()) {
                    $typeName = $node->type->type->name;
                    if ($this->isScalar($typeName)) {
                        continue;
                    }
                    $list = [];
                    $parser = (new ParserFactory())->create(
                        ParserFactory::PREFER_PHP7,
                        new Emulative([
                            'usedAttributes' => [
                                'comments',
                                'startLine',
                                'endLine',
                                'startFilePos',
                                'endFilePos',
                            ],
                        ])
                    );

                    /** @var ReflectionClass $class */
                    $class = $parameter->getDeclaringClass();
                    /** @var string $fileName */
                    $fileName = $class->getFileName();
                    /** @var string $phpFileContent */
                    $phpFileContent = file_get_contents($fileName);
                    /** @var Stmt[] $parsedFile */
                    $parsedFile = $parser->parse($phpFileContent);

                    $namespace = $this->getNamespaceStmt($parsedFile);
                    $uses = $this->getUseStmts($namespace);
                    $namespaces = $this->getUsesNamespaces($uses);

                    foreach ($data as $objectConstructorData) {
                        $list[] = $this->build(
                            $this->getFullClassName($typeName, $namespaces, $constructor->getDeclaringClass()),
                            $objectConstructorData
                        );
                    }

                    return $list;
                }
            }
        }

        return $data;
    }

    private function isScalar(string $value): bool
    {
        $scalars = [
            'string',
            'int',
            'float',
            'double',
            'mixed',
        ];

        return in_array($value, $scalars, true);
    }

    /**
     * @param Stmt[] $nodes
     */
    private function getNamespaceStmt(array $nodes): Stmt\Namespace_
    {
        foreach ($nodes as $node) {
            if ($node instanceof Stmt\Namespace_) {
                return $node;
            }
        }

        return new Stmt\Namespace_();
    }

    /** @return Stmt\Use_[] */
    private function getUseStmts(Stmt\Namespace_ $node): array
    {
        return array_filter($node->stmts, function (Stmt $node): bool {
            return $node instanceof Stmt\Use_;
        });
    }

    /**
     * @param Stmt\Use_[] $uses
     * @return string[]
     */
    private function getUsesNamespaces(array $uses): array
    {
        return array_map(function (Stmt\Use_ $use): string {
            return $use->uses[0]->name->toString();
        }, $uses);
    }

    /** @param string[] $namespaces */
    private function getFullClassName(string $name, array $namespaces, ReflectionClass $class): string
    {
        if ('\\' === $name[0]) {
            return $name;
        }

        if (0 === count($namespaces)) {
            return $class->getNamespaceName() . '\\' . $name;
        }

        return $this->getNamespaceForClass($name, $namespaces);
    }

    /**
     * @param string[] $namespaces
     * @throws BuildingError
     */
    private function getNamespaceForClass(string $className, array $namespaces): string
    {
        foreach ($namespaces as $namespace) {
            if ($this->endsWith($namespace, $className)) {
                return $namespace;
            }
        }

        throw new BuildingError('Can not resolve namespace for class ' . $className);
    }

    private function endsWith(string $haystack, string $needle): bool
    {
        $length = strlen($needle);

        return 0 === $length || (substr($haystack, -$length) === $needle);
    }
}
