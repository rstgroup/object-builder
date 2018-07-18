<?php declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Builder\ParameterNameStrategy;

use RstGroup\ObjectBuilder\Builder\ParameterNameStrategy;

final class SnakeCase implements ParameterNameStrategy
{
    public function isFulfilled(string $parameterName): bool
    {
        return preg_match('/^[^\s-]+$/i', $parameterName) === 1;
    }

    public function getName(string $parameterName): string
    {
        return $this->snakeCaseToCamelCase($parameterName);
    }

    private function snakeCaseToCamelCase(string $string): string
    {
        $str = str_replace('_', '', ucwords($string, '_'));

        return lcfirst($str);
    }
}
