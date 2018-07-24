<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Builder;

use RstGroup\ObjectBuilder\Builder;

final class Blueprint implements Builder
{
    private $blueprintFactory;

    public function __construct(Builder\Blueprint\Factory $factory)
    {
        $this->blueprintFactory = $factory;
    }

    public function build(string $class, array $data): object
    {
        $blueprint = $this->blueprintFactory->create($class);

        return $blueprint($data);
    }
}
