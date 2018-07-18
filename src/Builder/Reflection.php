<?php declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Builder;

use PhpParser\Node\Stmt;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use Roave\BetterReflection\BetterReflection;
use RstGroup\ObjectBuilder\Builder;
use RstGroup\ObjectBuilder\BuilderException;

final class Reflection implements Builder
{
    private $parameterNameStrategy;

    public function __construct(ParameterNameStrategy $parameterNameStrategy)
    {
        $this->parameterNameStrategy = $parameterNameStrategy;
    }

    /**
     * @param mixed[] $data
     * @throws BuilderException
     */
    public function build(string $class, array $data): object
    {
        try {
            $classReflection = new ReflectionClass($class);

            /** @var ReflectionMethod $constructorMethod */
            $constructor = $classReflection->getConstructor();

            $parameters = iterator_to_array($this->collect($constructor, $data));

            return new $class(...$parameters);
        } catch (ReflectionException $exception) {
            throw new BuilderException();
        }
    }

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
            }

            if ($parameter->isDefaultValueAvailable()) {
                yield $parameter->getDefaultValue();
            }
        }
    }

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
            $name = $class->getName();
            /** @var ReflectionMethod $constructorMethod */
            $constructorMethod = $class->getConstructor();
            $parameters = [];

            if (null !== $constructorMethod) {
                $parameters = iterator_to_array($this->collect($constructorMethod, $data));
            }

            return new $name(...$parameters);
        }

        if ($parameter->isArray()) {
            $parser = new PhpDocParser(new TypeParser(), new ConstExprParser());
            $node = $parser->parse(new TokenIterator((new Lexer())->tokenize($constructor->getDocComment())));
            foreach ($node->getParamTagValues() as $node) {
                if ($node->parameterName === '$' . $parameter->getName()) {
                    $type = $node->type->type;
                    $list = [];

                    $parser = (new BetterReflection())->phpParser();

                    $parsedFile = $parser->parse(file_get_contents($constructor->getDeclaringClass()->getFileName()));
                    $namespace = $this->getNamespaceStmt($parsedFile);
                    $use = $this->getUseStmt($namespace);
                    $namespaces = $this->getUsesNamespaces($use);

                    foreach($data as $objectConstructorData) {
                        $list[] = $this->build(
                            $this->getFullClassName($type->name, $namespaces, $constructor->getDeclaringClass()),
                            $objectConstructorData);
                    }

                    return $list;
                }
            }
        }

        return $data;
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

    private function getUseStmt(Stmt\Namespace_ $node): Stmt\Use_
    {
        foreach ($node->stmts as $node) {
            if ($node instanceof Stmt\Use_) {
                return $node;
            }
        }

        return new Stmt\Use_([]);
    }

    /** @return string[] */
    private function getUsesNamespaces(Stmt\Use_ $node): array
    {
        $names = [];
        foreach ($node->uses as $use) {
            $names[] = $use->name->toString();
        }

        return $names;
    }

    private function getFullClassName(string $name, array $namespaces, ReflectionClass $class): string
    {
        if ($name[0] === '\\') {
            return $name;
        }

        if (0 === count($namespaces)) {
            return $class->getNamespaceName() . '\\' . $name;
        }

        return $this->getNamespaceForClass($name, $namespaces);
    }

    /**
     * @param string[] $namespaces
     * @throws BuilderException
     */
    private function getNamespaceForClass(string $className, array $namespaces): string
    {
        foreach ($namespaces as $namespace) {
            if ($this->endsWith($namespace, $className)) {
                return $namespace;
            }
        }

        throw new BuilderException();
    }

    private function endsWith($haystack, $needle)
    {
        $length = strlen($needle);

        return $length === 0 || (substr($haystack, -$length) === $needle);
    }
}
