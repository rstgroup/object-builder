<?php

declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Test;

final class ListOfObjectsWithScalarTypedArrayConstructor
{
    /**
     * @param string[] $list1
     * @param mixed[] $list2
     */
    public function __construct(public array $list1, public array $list2)
    {
    }
}
