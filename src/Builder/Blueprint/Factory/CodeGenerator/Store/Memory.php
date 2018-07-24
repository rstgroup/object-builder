<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Store;

use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Store;
use Throwable;

final class Memory implements Store
{
    private $store = [];

    public function get(string $class): callable
    {
        try {
            return eval($this->store[$class]);
        } catch (Throwable $exception) {
            return null;
        }
    }

    public function save(string $class, string $blueprint): void
    {
        $this->store[$class] = $blueprint;
    }
}
