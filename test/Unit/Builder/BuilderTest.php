<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Test\Unit\Builder;

use PHPUnit\Framework\TestCase;
use RstGroup\ObjectBuilder\Builder;
use RstGroup\ObjectBuilder\Test\ListOfObjectsWithoutUseButWithFQNTypedArrayConstructor;
use RstGroup\ObjectBuilder\Test\ListOfObjectsWithoutUseStmtConstructor;
use RstGroup\ObjectBuilder\Test\ListOfObjectsWithScalarTypedArrayAndObjectListConstructor;
use RstGroup\ObjectBuilder\Test\ListOfObjectsWithScalarTypedArrayConstructor;
use RstGroup\ObjectBuilder\Test\ListOfObjectsWithUseStmtConstructor;
use RstGroup\ObjectBuilder\Test\Object\SomeObject;
use RstGroup\ObjectBuilder\Test\Object\SomeSecondObject;
use RstGroup\ObjectBuilder\Test\SimpleMixedConstructor;
use RstGroup\ObjectBuilder\Test\SimpleMixedConstructorWithDefaultValue;
use RstGroup\ObjectBuilder\Test\SimpleNullableConstructor;
use RstGroup\ObjectBuilder\Test\SimpleScalarConstructor;
use RstGroup\ObjectBuilder\Test\SomeAggregateRoot;
use RstGroup\ObjectBuilder\Test\SomeObjectWithEmptyConstructor;

abstract class BuilderTest extends TestCase
{
    /** @var Builder */
    protected static $builder;

    /** @test */
    public function iCanBuildSimpleObjectWithScalarValuesInConstructor(): void
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
    public function iCanBuildSimpleObjectWithScalarAndObjectValuesInConstructor(): void
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
    public function iCanBuildSimpleObjectWithDefaultValuesInConstructor(): void
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
    public function iCanBuildObjectWithObjectCollectionWithoutUseInConstructor(): void
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
        foreach ($object->list as $element) {
            $this->assertInstanceOf(SimpleScalarConstructor::class, $element);
        }
    }

    /** @test */
    public function iCanBuildObjectWithObjectCollectionWithUseInConstructor(): void
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
        foreach ($object->list as $element) {
            $this->assertInstanceOf(SomeSecondObject::class, $element);
        }
    }

    /** @test */
    public function iCanBuildObjectWithObjectCollectionWithoutUseButWithFQNTypedArrayInConstructor(): void
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
        foreach ($object->list as $element) {
            $this->assertInstanceOf(SomeObject::class, $element);
        }
    }

    /** @test */
    public function iCanBuildAdvancedObjectHierarchy(): void
    {
        $data = [
            'someString' => 'some string3',
            'simpleObject1' => [
                'someString' => 'some string1',
                'someInt' => 1,
            ],
            'simpleObject2' => [
                'someString' => 'some string2',
                'someInt' => 2,
                'someObject' => [],
            ],
            'someInt' => 3,
        ];
        $class = SomeAggregateRoot::class;

        /** @var SomeAggregateRoot $object */
        $object = static::$builder->build($class, $data);

        $this->assertInstanceOf(SomeAggregateRoot::class, $object);
        $this->assertSame('some string3', $object->someString);
        $this->assertSame(3, $object->someInt);
        $this->assertInstanceOf(SimpleScalarConstructor::class, $object->simpleObject1);
        $this->assertInstanceOf(SimpleMixedConstructor::class, $object->simpleObject2);
        $this->assertSame(1, $object->simpleObject1->someInt);
        $this->assertSame('some string1', $object->simpleObject1->someString);
        $this->assertSame(2, $object->simpleObject2->someInt);
        $this->assertSame('some string2', $object->simpleObject2->someString);
    }

    /** @test */
    public function iCanBuildObjectWithScalarCollectionTypedArrayInConstructor()
    {
        $data = [
            'list1' => ['str', 'str'],
            'list2' => ['str', 'str'],
        ];
        $class = ListOfObjectsWithScalarTypedArrayConstructor::class;

        /** @var ListOfObjectsWithScalarTypedArrayConstructor $object */
        $object = static::$builder->build($class, $data);

        $this->assertInstanceOf(ListOfObjectsWithScalarTypedArrayConstructor::class, $object);
        $this->assertCount(2, $object->list1);
        $this->assertCount(2, $object->list2);
        foreach($object->list1 as $element) {
            $this->assertSame('str', $element);
        }
        foreach($object->list2 as $element) {
            $this->assertSame('str', $element);
        }
    }

    /** @test */
    public function iCanBuildObjectWithBothScalarAndObjectCollectionTypedArrayInConstructor()
    {
        $data = [
            'list1' => ['str', 'str'],
            'list2' => [
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
        $class = ListOfObjectsWithScalarTypedArrayAndObjectListConstructor::class;

        /** @var ListOfObjectsWithScalarTypedArrayAndObjectListConstructor $object */
        $object = static::$builder->build($class, $data);

        $this->assertInstanceOf(ListOfObjectsWithScalarTypedArrayAndObjectListConstructor::class, $object);
        $this->assertCount(2, $object->list1);
        $this->assertCount(2, $object->list2);
        foreach($object->list1 as $element) {
            $this->assertSame('str', $element);
        }
        foreach($object->list2 as $element) {
            $this->assertInstanceOf(SimpleScalarConstructor::class, $element);
        }
    }

    /** @test */
    public function iCanBuildObjectWithNullableParameterWithoutDefaultValue()
    {
        $data = [
            'someString1' => 'some string1',
            'someString2' => 'some string2',
        ];
        $class = SimpleNullableConstructor::class;

        /** @var SimpleNullableConstructor $object */
        $object = static::$builder->build($class, $data);

        $this->assertInstanceOf(SimpleNullableConstructor::class, $object);
        $this->assertSame('some string1', $object->someString1);
        $this->assertSame('some string2', $object->someString2);
        $this->assertNull($object->someInt);
    }

    /** @test */
    public function iCanBuildObjectWithNullableParameterWithHimValueValue()
    {
        $data = [
            'someString1' => 'some string1',
            'someInt' => 123,
            'someString2' => 'some string2',
        ];
        $class = SimpleNullableConstructor::class;

        /** @var SimpleNullableConstructor $object */
        $object = static::$builder->build($class, $data);

        $this->assertInstanceOf(SimpleNullableConstructor::class, $object);
        $this->assertSame('some string1', $object->someString1);
        $this->assertSame('some string2', $object->someString2);
        $this->assertSame(123, $object->someInt);
    }
}
