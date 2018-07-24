<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Node;

use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Node;

final class Scalar extends Node
{
    public function __toString(): string
    {
        return sprintf('[\'%s\']', $this->name);
    }
}
