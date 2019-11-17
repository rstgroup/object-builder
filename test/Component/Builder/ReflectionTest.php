<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Test\Component\Builder;

use RstGroup\ObjectBuilder\Builder\ParameterNameStrategy\SnakeCase;
use RstGroup\ObjectBuilder\Builder\Reflection;
use RstGroup\ObjectBuilder\BuildingError;
use RstGroup\ObjectBuilder\Test\Object\MixedConstructor;

class ReflectionTest extends BuilderTest
{
    public static function setUpBeforeClass(): void
    {
        static::$builder = new Reflection(new SnakeCase());
    }

    /** @test */
    public function tooFewArgumentsWillThrowBuildingException(): void
    {
        $data = [
            'some_string' => 'some string',
            'some_int' => 999,
        ];
        $class = MixedConstructor::class;

        $this->expectException(BuildingError::class);

        static::$builder->build($class, $data);
    }
}
