<?php declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Node;

use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Node;

final class ObjectList extends Node
{
    private $objectNode;

    public function __construct(string $name, Node $objectNode)
    {
        parent::__construct($objectNode->type(), $name);
        $this->objectNode = $objectNode;
    }

    public function __toString(): string
    {
        return sprintf('(function (array $list) {
            $arr = [];
            foreach ($list as $data) {
                $arr[] = %s;
            }
            
            return $arr;
        })($data[\'' . $this->name() . '\'])', $this->objectNode);
    }
}
