<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator;

interface PatternStore
{
    public function get(string $class): ?string;
    public function save(string $class, string $blueprint): void;
}
