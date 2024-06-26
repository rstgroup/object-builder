<?php

declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Builder;

use Iterator;
use PhpParser\Node\Stmt;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use Roave\BetterReflection\BetterReflection;
use RstGroup\ObjectBuilder\Builder;
use RstGroup\ObjectBuilder\BuilderException;
use Throwable;

final class Reflection implements Builder
{
    public function __construct(private readonly ParameterNameStrategy $parameterNameStrategy)
    {
    }

    /**
     * @throws BuilderException
     */
    public function build(string $class, array $data): object
    {
        try {
            $classReflection = new ReflectionClass($class);

            /** @var ReflectionMethod|null $constructor */
            $constructor = $classReflection->getConstructor();

            $parameters = iterator_to_array($this->collect($constructor, $data));

            return new $class(...$parameters);
        } catch (Throwable $throwable) {
            throw new BuilderException('Cant build object', 0, $throwable);
        }
    }

    private function collect(ReflectionMethod $constructor, array $data): Iterator
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
     * @throws BuilderException
     * @throws ReflectionException
     */
    private function buildParameter(ReflectionParameter $parameter, mixed $data, ReflectionMethod $constructor): mixed
    {
        $parameterType = $parameter->getType();

        if ($parameterType instanceof ReflectionNamedType && !$parameterType->isBuiltin()) {
            $parameterClass = new ReflectionClass($parameterType->getName());

            $name = $parameterClass->getName();
            /** @var ReflectionMethod $constructorMethod */
            $constructorMethod = $parameterClass->getConstructor();
            $parameters = [];

            if (null !== $constructorMethod) {
                $parameters = iterator_to_array($this->collect($constructorMethod, $data));
            }

            return new $name(...$parameters);
        }

        if ($parameterType instanceof ReflectionNamedType && $parameterType->getName() === 'array') {
            $parser = new PhpDocParser(new TypeParser(), new ConstExprParser());
            $node = $parser->parse(new TokenIterator((new Lexer())->tokenize($constructor->getDocComment())));
            foreach ($node->getParamTagValues() as $node) {
                if ($node->parameterName === '$' . $parameter->getName()) {
                    $typeName = $node->type->type->name;
                    if ($this->isScalar($typeName)) {
                        continue;
                    }

                    $list = [];

                    $parser = (new BetterReflection())->phpParser();

                    $parsedFile = $parser->parse(file_get_contents($constructor->getDeclaringClass()->getFileName()));
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

        return in_array($value, $scalars);
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
        $uses = [];
        foreach ($node->stmts as $node) {
            if ($node instanceof Stmt\Use_) {
                $uses[] = $node;
            }
        }

        return $uses;
    }

    /**
     * @param Stmt\Use_[] $uses
     * @return string[]
     */
    private function getUsesNamespaces(array $uses): array
    {
        $names = [];
        foreach ($uses as $use) {
            $names[] = $use->uses[0]->name->toString();
        }

        return $names;
    }

    private function getFullClassName(string $name, array $namespaces, ReflectionClass $class): string
    {
        if ($name[0] === '\\') {
            return $name;
        }

        if ([] === $namespaces) {
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

        throw new BuilderException('Can not resolve namespace for class ' . $className);
    }

    private function endsWith(string $haystack, string $needle): bool
    {
        $length = strlen($needle);

        return $length === 0 || (substr($haystack, -$length) === $needle);
    }
}
