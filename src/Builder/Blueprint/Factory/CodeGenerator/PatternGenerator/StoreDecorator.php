<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\PatternGenerator;

use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\PatternGenerator;
use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\PatternStore;

final class StoreDecorator implements PatternGenerator
{
    /** @var PatternStore */
    private $store;
    /** @var PatternGenerator */
    private $generator;

    public function __construct(PatternStore $store, PatternGenerator $generator)
    {
        $this->store = $store;
        $this->generator = $generator;
    }

    public function create(string $class): string
    {
        return $this->store->get($class) ?? $this->generator->create($class);
    }
}
