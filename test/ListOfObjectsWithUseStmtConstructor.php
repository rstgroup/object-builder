<?php declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Test;

use RstGroup\ObjectBuilder\Test\Object\SomeObject;

class ListOfObjectsWithUseStmtConstructor
{
    public $list;

    /**
     * @param SomeObject[] $list
     */
    public function __construct(array $list)
    {
        $this->list = $list;
    }
}
