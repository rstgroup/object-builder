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
    /** @var bool */
    private $nullable;

    /** @param mixed $defaultValue */
    public function __construct(string $type, string $name, bool $nullable, $defaultValue)
    {
        $this->name = $name;
        $this->defaultValue = $defaultValue;
        $this->type = $type;
        $this->nullable = $nullable;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function hasDefaultValue(): bool
    {
        if ($this->nullable()) {
            return true;
        }

        return null !== $this->defaultValue;
    }

    public function nullable(): bool
    {
        return $this->nullable;
    }

    /** @return mixed */
    public function defaultValue()
    {
        return $this->defaultValue;
    }
}
