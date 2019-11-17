<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Pattern\Generator;

use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Pattern\Generator;

/** @codeCoverageIgnore */
class Dummy implements Generator
{
    /** @var string[] */
    private $store;

    /** @param mixed[] $store */
    public function __construct(array $store = [])
    {
        $this->store = $store;
    }

    public function create(string $class): string
    {
        return $this->store[$class];
    }
}
