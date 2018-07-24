<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Test;

class SimpleMixedConstructorWithDefaultValue
{
    public $someString;
    public $someInt;
    public $someObject;

    public function __construct(
        SomeObjectWithEmptyConstructor $someObject,
        string $someString = 'some string',
        int $someInt = 999
    ) {
        $this->someString = $someString;
        $this->someInt = $someInt;
        $this->someObject = $someObject;
    }
}
