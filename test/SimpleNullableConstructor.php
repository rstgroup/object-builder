<?php

declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Test;

final class SimpleNullableConstructor
{
    public function __construct(public string $someString1, public ?int $someInt, public string $someString2)
    {
    }
}
