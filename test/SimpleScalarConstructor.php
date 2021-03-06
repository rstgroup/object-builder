<?php declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Test;

class SimpleScalarConstructor
{
    public $someString;
    public $someInt;

    public function __construct(string $someString, int $someInt)
    {
        $this->someString = $someString;
        $this->someInt = $someInt;
    }
}
