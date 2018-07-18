<?php declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Builder\Blueprint;

interface Store
{
    public function get(string $class): callable;
    public function save(string $class, callable $blueprint): void;
}
