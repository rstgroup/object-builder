<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder;

interface Builder
{
    /** @param mixed[] $data */
    public function build(string $class, array $data): object;
}
