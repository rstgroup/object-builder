<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Test\Object\Collection;

class WithoutUseStmtConstructor
{
    public $list;

    /**
     * @param ScalarConstructor[] $list
     */
    public function __construct(array $list)
    {
        $this->list = $list;
    }
}
