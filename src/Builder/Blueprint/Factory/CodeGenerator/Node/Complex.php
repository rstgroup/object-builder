<?php declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Node;

use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Node;

final class Complex extends Node
{
    /** @var Node */
    private $nodes = [];
    private $class;

    public function __construct(string $class, string $name = '', $defaultValue = null)
    {
        parent::__construct($name, $defaultValue);
        $this->class = $class;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function add(Node $node): void
    {
        $this->nodes[] = $node;
    }

    /** @return Node[] */
    public function innerNodes(): array
    {
        return $this->nodes;
    }

    public function __toString(): string
    {
        return sprintf(
            'new %s(%s)',
            $this->getClass(),
            implode(', ', $this->nodes)
        );
    }
}
