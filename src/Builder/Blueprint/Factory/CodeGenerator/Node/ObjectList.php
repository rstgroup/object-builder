<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Node;

use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Node;

final class ObjectList extends Node
{
    /** @var Node */
    private $objectNode;

    public function __construct(string $name, Node $objectNode)
    {
        parent::__construct(
            $objectNode->type(),
            $name,
            false,
            null
        );
        $this->objectNode = $objectNode;
    }

    public function objectNode(): Node
    {
        return $this->objectNode;
    }
}
