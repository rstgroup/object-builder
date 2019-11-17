<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Node;

use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Node;

final class Composite extends Node
{
    /** @var Node[] */
    private $nodes = [];

    public function add(Node $node): void
    {
        $this->nodes[] = $node;
    }

    /** @return Node[] */
    public function innerNodes(): iterable
    {
        return $this->nodes;
    }
}
