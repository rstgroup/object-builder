<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Test\Object;

class MixedConstructor
{
    public $someString;
    public $someInt;
    public $someObject;

    public function __construct(string $someString, int $someInt, EmptyConstructor $someObject)
    {
        $this->someString = $someString;
        $this->someInt = $someInt;
        $this->someObject = $someObject;
    }
}
