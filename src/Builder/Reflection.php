<?php declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Builder;

use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
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
                    foreach($data as $objectConstructorData) {
                        $list[] = $this->build($type->name, $objectConstructorData);
                    }
                    return $list;
                }
            }
        }

        return $data;
    }
}
