<?php

declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Test;

final class SimpleScalarConstructor
{
    public function __construct(public string $someString, public int $someInt)
    {
    }
}
