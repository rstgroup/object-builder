<?php

declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Test;

final class ListOfObjectsWithoutUseStmtConstructor
{
    /**
     * @param SimpleScalarConstructor[] $list
     */
    public function __construct(public array $list)
    {
    }
}
