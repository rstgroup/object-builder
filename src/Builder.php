<?php declare(strict_types=1);

namespace RstGroup\ObjectBuilder;

interface Builder
{
    public function build(string $class, array $data): object;
}
