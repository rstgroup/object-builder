<?php

declare(strict_types=1);

namespace RstGroup\ObjectBuilder;

interface Builder
{
    /**
     * @param string $class
     * @param mixed[] $data
     * @return object
     */
    public function build(string $class, array $data): object;
}
