<?php declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Test;

class SomeAggregateRoot
{
    public $someString;
    public $simpleObject1;
    public $simpleObject2;
    public $someInt;

    public function __construct(
        string $someString,
        SimpleScalarConstructor $simpleObject1,
        SimpleMixedConstructor $simpleObject2,
        int $someInt
    ) {
        $this->someString = $someString;
        $this->simpleObject1 = $simpleObject1;
        $this->simpleObject2 = $simpleObject2;
        $this->someInt = $someInt;
    }
}
