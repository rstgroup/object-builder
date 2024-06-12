<?php

declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Test;

final class SomeAggregateRoot
{
    public function __construct(
        public string $someString,
        public SimpleScalarConstructor $simpleObject1,
        public SimpleMixedConstructor $simpleObject2,
        public int $someInt
    ) {
    }
}
