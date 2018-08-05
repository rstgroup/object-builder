<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Test\Object\Collection;

class WithoutUseButWithFQNTypedArrayConstructor
{
    public $list;

    /**
     * @param \RstGroup\ObjectBuilder\Test\Object\EmptyConstructor[] $list
     */
    public function __construct(array $list)
    {
        $this->list = $list;
    }
}
