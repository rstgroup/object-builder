<?php

declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Test;

use RstGroup\ObjectBuilder\Test\Object\SomeObject;
use RstGroup\ObjectBuilder\Test\Object\SomeSecondObject;

final class ListOfObjectsWithUseStmtConstructor
{
    /**
     * @var SomeObject
     */
    public SomeObject $object;

    /**
     * @param SomeSecondObject[] $list
     */
    public function __construct(public array $list)
    {
        $this->object = new SomeObject();
    }
}
