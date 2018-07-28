<?php declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Node\Serializer;

use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Node;
use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Node\Serializer;
use RstGroup\ObjectBuilder\BuildingError;

final class ArrayAccess implements Serializer
{
    private const SCALAR_PATTERN = '[\'%s\']';
    private const COMPLEX_PATTERN = 'new %s(%s)';
    private const OBJECT_LIST_PATTERN =
'(function (array $list) {
        $arr = [];
        foreach ($list as $data) {
            $arr[] = %s;
        }
        
        return $arr;
    })($data[\'%s\'])';

    public function serialize(Node $node): string
    {
        switch (get_class($node)) {
            case Node\Scalar::class:
                return $this->serializeScalarNode($node);
                break;
            case Node\Complex::class:
                return $this->serializeComplexNode($node);
                break;
            case Node\ObjectList::class:
                return $this->serializeObjectListNode($node);
                break;
        }

        throw new BuildingError();
    }

    private function serializeScalarNode(Node\Scalar $node): string
    {
        return sprintf(static::SCALAR_PATTERN, $node->name());
    }

    private function serializeComplexNode(Node\Complex $node): string
    {
        $nodes = [];
        foreach ($node->innerNodes() as $innerNode) {
            if ($innerNode instanceof Node\Scalar) {
                $serializedInnerNode = $this->serialize($innerNode);
                if ('' !== $node->name()) {
                    $serializedInnerNode = '[\'' . $node->name() . '\']' . $serializedInnerNode;
                }
                $nodes[] = '$data' . $serializedInnerNode;
                continue;
            }
            $nodes[] = $this->serialize($innerNode);
        }

        return sprintf(
            self::COMPLEX_PATTERN,
            $node->type(),
            implode(', ', $nodes)
        );
    }

    private function serializeObjectListNode(Node\ObjectList $node): string
    {
        return sprintf(
            static::OBJECT_LIST_PATTERN,
            $this->serialize($node->objectNode()),
            $node->name()
        );
    }
}
