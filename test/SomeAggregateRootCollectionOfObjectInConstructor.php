<?php declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Test;

class SomeAggregateRootCollectionOfObjectInConstructor
{
    public $list;

    /**
     * @param SimpleScalarConstructor[] $list
     */
    public function __construct(array $list)
    {
        $this->list = $list;
    }
}
