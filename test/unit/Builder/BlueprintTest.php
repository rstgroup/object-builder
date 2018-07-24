<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Test\unit\Builder;

use RstGroup\ObjectBuilder\Builder\Blueprint;

class BlueprintTest extends BuilderTest
{
    public static function setUpBeforeClass(): void
    {
        static::$builder = new Blueprint(
            new Blueprint\Factory\CodeGenerator()
        );
    }
}
