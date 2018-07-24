<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Test;

class ListOfObjectsWithoutUseButWithFQNTypedArrayConstructor
{
    public $list;

    /**
     * @param \RstGroup\ObjectBuilder\Test\Object\SomeObject[] $list
     */
    public function __construct(array $list)
    {
        $this->list = $list;
    }
}
