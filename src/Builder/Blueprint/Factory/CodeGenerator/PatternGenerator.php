<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator;

interface PatternGenerator
{
    public function create(string $class): string;
}
