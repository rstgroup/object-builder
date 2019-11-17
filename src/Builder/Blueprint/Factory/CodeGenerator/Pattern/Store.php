<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Pattern;

interface Store
{
    public function get(string $class): ?string;
    public function save(string $class, string $blueprint): void;
}
