<?php declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Test;

use RstGroup\ObjectBuilder\Test\Object\SomeObject;
use RstGroup\ObjectBuilder\Test\Object\SomeSecondObject;

class ListOfObjectsWithUseStmtConstructor
{
    public $list;
    public $object;

    /**
     * @param SomeSecondObject[] $list
     */
    public function __construct(array $list)
    {
        $this->list = $list;
        $this->object = new SomeObject();
    }
}
