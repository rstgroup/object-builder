<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Test\Unit\Builder\Blueprint\Factory;

use RstGroup\ObjectBuilder\Builder\Blueprint\Factory;

final class Simple implements Factory
{
    private $blueprint;

    public function __construct(callable $blueprint)
    {
        $this->blueprint = $blueprint;
    }

    public function create(string $class): callable
    {
        return $this->blueprint;
    }
}
