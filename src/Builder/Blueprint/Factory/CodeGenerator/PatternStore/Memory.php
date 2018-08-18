<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\PatternStore;

use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\PatternStore;

final class Memory implements PatternStore
{
    /** @var string[] */
    private $store = [];

    /** @param string[] $blueprints */
    public function __construct(array $blueprints = [])
    {
        $this->store = $blueprints;
    }

    public function get(string $class): ?string
    {
        if (! isset($this->store[$class])) {
            return null;
        }

        return $this->store[$class];
    }

    public function save(string $class, string $blueprint): void
    {
        $this->store[$class] = $blueprint;
    }

    /** @return string[] */
    public function store(): array
    {
        return $this->store;
    }
}
