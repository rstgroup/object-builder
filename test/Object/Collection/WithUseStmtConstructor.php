<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Test\Object\Collection;

use RstGroup\ObjectBuilder\Test\Object\EmptyConstructor;
use RstGroup\ObjectBuilder\Test\Object\SecondEmptyConstructor;

class WithUseStmtConstructor
{
    public $list;
    public $object;

    /**
     * @param SecondEmptyConstructor[] $list
     */
    public function __construct(array $list)
    {
        $this->list = $list;
        $this->object = new EmptyConstructor();
    }
}
