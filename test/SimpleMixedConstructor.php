<?php

declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Test;

final class SimpleMixedConstructor
{
    public function __construct(public string $someString, public int $someInt, public SomeObjectWithEmptyConstructor $someObject)
    {
    }
}
