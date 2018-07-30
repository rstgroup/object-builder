<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Test\Unit\Builder;

use RstGroup\ObjectBuilder\Builder\ParameterNameStrategy\Simple;
use RstGroup\ObjectBuilder\Builder\Reflection;

class ReflectionTest extends BuilderTest
{
    public static function setUpBeforeClass(): void
    {
        static::$builder = new Reflection(new Simple());
    }
}
