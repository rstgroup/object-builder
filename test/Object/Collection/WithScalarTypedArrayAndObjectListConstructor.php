<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Test\Object\Collection;

class WithScalarTypedArrayAndObjectListConstructor
{
    public $list1;
    public $list2;

    /**
     * @param string[] $list1
     * @param ScalarConstructor[] $list2
     */
    public function __construct(array $list1, array $list2)
    {
        $this->list1 = $list1;
        $this->list2 = $list2;
    }
}
