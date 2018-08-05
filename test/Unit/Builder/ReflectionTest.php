<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Test\Unit\Builder;

use RstGroup\ObjectBuilder\Builder\ParameterNameStrategy\Simple;
use RstGroup\ObjectBuilder\Builder\Reflection;
use RstGroup\ObjectBuilder\BuildingError;
use RstGroup\ObjectBuilder\Test\MixedConstructor;

class ReflectionTest extends BuilderTest
{
    public static function setUpBeforeClass(): void
    {
        static::$builder = new Reflection(new Simple());
    }

    public function tooFewArgumentsWillTHrowBuildingException(): void
    {
        $data = [
            'someString' => 'some string',
            'someInt' => 999,
        ];
        $class = MixedConstructor::class;

        $this->expectException(BuildingError::class);

        static::$builder->build($class, $data);
    }
}
