<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder;

use ReflectionParameter;

interface PhpDocParser
{
    public function isListOfObject(
        string $comment,
        ReflectionParameter $parameter
    ): bool;

    public function getListType(
        string $comment,
        ReflectionParameter $parameter
    ): string;
}
