<?php

declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Builder\ParameterNameStrategy;

use RstGroup\ObjectBuilder\Builder\ParameterNameStrategy;

final class Simple implements ParameterNameStrategy
{
    public function isFulfilled(string $parameterName): bool
    {
        return preg_match('/^[^\s_-]+$/i', $parameterName) === 1;
    }

    public function getName(string $parameterName): string
    {
        return $parameterName;
    }
}
