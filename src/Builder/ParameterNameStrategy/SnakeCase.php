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

    private function snakeCaseToCamelCase(string $string, bool $capitalizeFirstCharacter = false): string
    {
        $str = str_replace('_', '', ucwords($string, '_'));

        if (! $capitalizeFirstCharacter) {
            $str = lcfirst($str);
        }

        return $str;
    }
}
