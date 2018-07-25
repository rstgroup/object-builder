<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator;

abstract class Node
{
    /** @var string */
    protected $name;
    /** @var mixed|null */
    private $defaultValue;
    /** @var string */
    private $type;

    /** @param mixed $defaultValue */
    public function __construct(string $type, string $name, $defaultValue = null)
    {
        $this->name = $name;
        $this->defaultValue = $defaultValue;
        $this->type = $type;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function withDefaultValue(): bool
    {
        return null !== $this->defaultValue;
    }

    /** @return mixed */
    public function defaultValue()
    {
        return $this->defaultValue;
    }

    abstract public function __toString(): string;
}
