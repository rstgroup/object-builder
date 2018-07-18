<?php declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Test\unit\Builder;

use PHPUnit\Framework\TestCase;
use RstGroup\ObjectBuilder\Builder\ParameterNameStrategy\Simple;
use RstGroup\ObjectBuilder\Builder\Reflection;
use RstGroup\ObjectBuilder\Test\ListOfObjectsWithoutUseButWithFQNTypedArrayConstructor;
use RstGroup\ObjectBuilder\Test\ListOfObjectsWithoutUseStmtConstructor;
use RstGroup\ObjectBuilder\Test\ListOfObjectsWithUseStmtConstructor;
use RstGroup\ObjectBuilder\Test\Object\SomeObject;
use RstGroup\ObjectBuilder\Test\Object\SomeSecondObject;
use RstGroup\ObjectBuilder\Test\SimpleMixedConstructor;
use RstGroup\ObjectBuilder\Test\SimpleMixedConstructorWithDefaultValue;
use RstGroup\ObjectBuilder\Test\SimpleScalarConstructor;
use RstGroup\ObjectBuilder\Test\SomeAggregateRoot;
use RstGroup\ObjectBuilder\Test\SomeObjectWithEmptyConstructor;

class ReflectionTest extends TestCase
{
    /** @var Reflection */
    private static $builder;

    public static function setUpBeforeClass()
    {
        static::$builder = new Reflection(new Simple());
    }

    /** @test */
    public function iCanBuildSimpleObjectWithScalarValuesInConstructor()
    {
        $data = [
            'someString' => 'some string',
            'someInt' => 999,
        ];
        $class = SimpleScalarConstructor::class;

        /** @var SimpleScalarConstructor $object */
        $object = static::$builder->build($class, $data);

        $this->assertInstanceOf(SimpleScalarConstructor::class, $object);
        $this->assertSame('some string', $object->someString);
        $this->assertSame(999, $object->someInt);
    }

    /** @test */
    public function iCanBuildSimpleObjectWithScalarAndObjectValuesInConstructor()
    {
        $data = [
            'someString' => 'some string',
            'someInt' => 999,
            'someObject' => [],
        ];
        $class = SimpleMixedConstructor::class;

        /** @var SimpleMixedConstructor $object */
        $object = static::$builder->build($class, $data);

        $this->assertInstanceOf(SimpleMixedConstructor::class, $object);
        $this->assertSame('some string', $object->someString);
        $this->assertSame(999, $object->someInt);
        $this->assertInstanceOf(SomeObjectWithEmptyConstructor::class, $object->someObject);
    }

    /** @test */
    public function iCanBuildSimpleObjectWithDefaultValuesInConstructor()
    {
        $data = [
            'someObject' => [],
        ];
        $class = SimpleMixedConstructorWithDefaultValue::class;

        /** @var SimpleMixedConstructorWithDefaultValue $object */
        $object = static::$builder->build($class, $data);

        $this->assertInstanceOf(SimpleMixedConstructorWithDefaultValue::class, $object);
        $this->assertSame('some string', $object->someString);
        $this->assertSame(999, $object->someInt);
        $this->assertInstanceOf(SomeObjectWithEmptyConstructor::class, $object->someObject);
    }

    /** @test */
    public function iCanBuildObjectWithObjectCollectionWithoutUseInConstructor()
    {
        $data = [
            'list' => [
                [
                    'someString' => 'some string1',
                    'someInt' => 1,
                ],
                [
                    'someString' => 'some string2',
                    'someInt' => 2,
                ],
            ],
        ];
        $class = ListOfObjectsWithoutUseStmtConstructor::class;

        /** @var ListOfObjectsWithoutUseStmtConstructor $object */
        $object = static::$builder->build($class, $data);

        $this->assertInstanceOf(ListOfObjectsWithoutUseStmtConstructor::class, $object);
        $this->assertCount(2, $object->list);
        foreach($object->list as $element) {
            $this->assertInstanceOf(SimpleScalarConstructor::class, $element);
        }
    }

    /** @test */
    public function iCanBuildObjectWithObjectCollectionWithUseInConstructor()
    {
        $data = [
            'list' => [
                [],
                [],
            ],
        ];
        $class = ListOfObjectsWithUseStmtConstructor::class;

        /** @var ListOfObjectsWithUseStmtConstructor $object */
        $object = static::$builder->build($class, $data);

        $this->assertInstanceOf(ListOfObjectsWithUseStmtConstructor::class, $object);
        $this->assertCount(2, $object->list);
        foreach($object->list as $element) {
            $this->assertInstanceOf(SomeSecondObject::class, $element);
        }
    }

    /** @test */
    public function iCanBuildObjectWithObjectCollectionWithoutUseButWithFQNTypedArrayInConstructor()
    {
        $data = [
            'list' => [
                [],
                [],
            ],
        ];
        $class = ListOfObjectsWithoutUseButWithFQNTypedArrayConstructor::class;

        /** @var ListOfObjectsWithoutUseButWithFQNTypedArrayConstructor $object */
        $object = static::$builder->build($class, $data);

        $this->assertInstanceOf(ListOfObjectsWithoutUseButWithFQNTypedArrayConstructor::class, $object);
        $this->assertCount(2, $object->list);
        foreach($object->list as $element) {
            $this->assertInstanceOf(SomeObject::class, $element);
        }
    }

    /** @test */
    public function iCanBuildAdvancedObjectHierarchy()
    {
        $data = [
            'someString' => 'some string',
            'simpleObject1' => [
                'someString' => 'some string',
                'someInt' => 1,
            ],
            'simpleObject2' => [
                'someString' => 'some string',
                'someInt' => 2,
                'someObject' => [],
            ],
            'someInt' => 3,
        ];
        $class = SomeAggregateRoot::class;

        /** @var SomeAggregateRoot $object */
        $object = static::$builder->build($class, $data);

        $this->assertInstanceOf(SomeAggregateRoot::class, $object);
        $this->assertSame('some string', $object->someString);
        $this->assertSame(3, $object->someInt);
        $this->assertInstanceOf(SimpleScalarConstructor::class, $object->simpleObject1);
        $this->assertInstanceOf(SimpleMixedConstructor::class, $object->simpleObject2);
        $this->assertSame(1, $object->simpleObject1->someInt);
        $this->assertSame(2, $object->simpleObject2->someInt);
    }
}
