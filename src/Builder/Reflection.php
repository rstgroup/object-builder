<?php declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Builder;

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
            $commandReflection = new ReflectionClass($class);
            /** @var ReflectionMethod $constructorMethod */
            $constructorMethod = $commandReflection->getConstructor();
            $parameters = $this->collectParameters($constructorMethod, $data);

            return new $class(...$parameters);
        } catch (ReflectionException $exception) {
            throw new BuilderException();
        }
    }

    /**
     * @param mixed[] $data
     * @return mixed[]
     */
    private function collectParameters(ReflectionMethod $constructor, array $data): array
    {
        $parameters = [];

        foreach ($constructor->getParameters() as $parameter) {
            $name = $parameter->getName();

            if (in_array($name, $this->denormalizeKeys($data), true)) {
                $parsedData = [];

                foreach ($data as $key => $value) {
                    $parsedData[$this->parameterNameStrategy->getName($key)] = $value;
                }
                $parameters[] = $this->buildParameter(
                    $parameter,
                    $parsedData[$name]
                );

                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $parameters[] = $parameter->getDefaultValue();

                continue;
            }
        }

        return $parameters;
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    private function buildParameter(ReflectionParameter $parameter, $data)
    {
        $class = $parameter->getClass();

        if (null !== $class) {
            $name = $class->getName();
            /** @var ReflectionMethod $constructorMethod */
            $constructorMethod = $class->getConstructor();
            $parameters = [];

            if (null !== $constructorMethod) {
                $parameters = $this->collectParameters(
                    $constructorMethod,
                    $data
                );
            }

            return new $name(...$parameters);
        }

        return $data;
    }

    /**
     * @param string[] $data
     * @return string[]
     */
    private function denormalizeKeys(array $data): array
    {
        $keys = [];

        foreach (array_keys($data) as $key) {
            $keys[] = $this->parameterNameStrategy->getName($key);
        }

        return $keys;
    }
}
