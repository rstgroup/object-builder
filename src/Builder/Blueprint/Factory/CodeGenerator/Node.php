<?php declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator;

abstract class Node
{
    protected $name;
    private $defaultValue;

    public function __construct(string $name, $defaultValue = null)
    {
        $this->name = $name;
        $this->defaultValue = $defaultValue;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function withDefaultValue(): bool
    {
        return null !== $this->defaultValue;
    }

    public function defaultValue()
    {
        return $this->defaultValue;
    }

    abstract public function __toString(): string;
}
