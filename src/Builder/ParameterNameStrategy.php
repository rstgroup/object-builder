<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Builder;

interface ParameterNameStrategy
{
    public function isFulfilled(string $parameterName): bool;
    public function getName(string $parameterName): string;
}
