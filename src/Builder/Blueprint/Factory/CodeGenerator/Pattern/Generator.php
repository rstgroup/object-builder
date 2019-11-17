<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Pattern;

interface Generator
{
    public function create(string $class): string;
}
