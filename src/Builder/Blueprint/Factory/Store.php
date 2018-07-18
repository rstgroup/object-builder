<?php declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Builder\Blueprint\Factory;

use RstGroup\ObjectBuilder\Builder\Blueprint\Factory;
use RstGroup\ObjectBuilder\Builder\Blueprint\Store as BlueprintStore;

final class Store implements Factory
{
    /** @var BlueprintStore */
    private $store;
    /** @var Factory */
    private $factory;

    public function __construct(BlueprintStore $store, Factory $factory)
    {
        $this->store = $store;
        $this->factory = $factory;
    }

    public function create(string $class): callable
    {
        $blueprint = $this->store->get($class);

        if (null === $blueprint) {
            $blueprint = $this->factory->create($class);
            $this->store->save($class, $blueprint);
        }

        return $blueprint;
    }
}
