<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\PatternGenerator;

use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\PatternGenerator;

/** @codeCoverageIgnore */
class Dummy implements PatternGenerator
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
