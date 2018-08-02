<?php declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Test;

class SimpleNullableConstructor
{
    public $someInt;
    public $someString1;
    public $someString2;

    public function __construct(string $someString1, ?int $someInt, string $someString2)
    {
        $this->someInt = $someInt;
        $this->someString1 = $someString1;
        $this->someString2 = $someString2;
    }
}
