<?php

declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Test;

final class SimpleMixedConstructorWithDefaultValue
{
    public function __construct(
        public SomeObjectWithEmptyConstructor $someObject,
        public string $someString = 'some string',
        public int $someInt = 999
    ) {
    }
}
