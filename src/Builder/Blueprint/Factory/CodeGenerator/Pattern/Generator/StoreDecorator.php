<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Pattern\Generator;

use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Pattern\Generator;
use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Pattern\Store;

final class StoreDecorator implements Generator
{
    /** @var Store */
    private $store;
    /** @var Generator */
    private $generator;

    public function __construct(Store $store, Generator $generator)
    {
        $this->store = $store;
        $this->generator = $generator;
    }

    public function create(string $class): string
    {
        return $this->store->get($class) ?? $this->generator->create($class);
    }
}
