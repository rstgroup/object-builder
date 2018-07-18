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
    /**
     * @param mixed[] $data
     *
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

            if (isset($data[$name])) {
                $parameters[] = $this->buildParameter(
                    $parameter,
                    $data[$name]
                );

                continue;
            }

            if (isset($data[$name])) {
                $parameters[] = $this->buildParameter(
                    $parameter,
                    $data[$name]
                );

                continue;
            }

            if (in_array($name, $this->denormalizeKeys($data), true)) {
                $parsedData = [];

                foreach ($data as $key => $value) {
                    $parsedData[$this->underscoresToCamelCase($key)] = $value;
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
            $keys[] = $this->underscoresToCamelCase($key);
        }

        return $keys;
    }

    private function underscoresToCamelCase(string $string, bool $capitalizeFirstCharacter = false): string
    {
        $str = str_replace('_', '', ucwords($string, '_'));

        if (! $capitalizeFirstCharacter) {
            $str = lcfirst($str);
        }

        return $str;
    }
}
