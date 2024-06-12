<?php

declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Test;

use RstGroup\ObjectBuilder\Test\Object\SomeObject;

final class ListOfObjectsWithoutUseButWithFQNTypedArrayConstructor
{
    /**
     * @param SomeObject[] $list
     */
    public function __construct(public array $list)
    {
    }
}
