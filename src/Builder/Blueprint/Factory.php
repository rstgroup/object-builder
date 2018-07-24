<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Builder\Blueprint;

interface Factory
{
    public function create(string $class): callable;
}
