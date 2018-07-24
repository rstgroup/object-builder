<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Test;

class SimpleMixedConstructor
{
    public $someString;
    public $someInt;
    public $someObject;

    public function __construct(string $someString, int $someInt, SomeObjectWithEmptyConstructor $someObject)
    {
        $this->someString = $someString;
        $this->someInt = $someInt;
        $this->someObject = $someObject;
    }
}
