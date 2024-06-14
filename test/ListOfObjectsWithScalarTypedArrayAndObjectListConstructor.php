<?php

declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Test;

final class ListOfObjectsWithScalarTypedArrayAndObjectListConstructor
{
    /**
     * @param string[] $list1
     * @param SimpleScalarConstructor[] $list2
     */
    public function __construct(public array $list1, public array $list2)
    {
    }
}
