<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Test\Object;

class MixedConstructorWithDefaultValue
{
    public $someString;
    public $someInt;
    public $someObject;

    public function __construct(
        EmptyConstructor $someObject,
        string $someString = 'some string',
        int $someInt = 999
    ) {
        $this->someString = $someString;
        $this->someInt = $someInt;
        $this->someObject = $someObject;
    }
}
