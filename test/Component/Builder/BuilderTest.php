<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Test\Component\Builder;

use PHPUnit\Framework\TestCase;
use RstGroup\ObjectBuilder\Builder;
use RstGroup\ObjectBuilder\Test\Object\Collection;
use RstGroup\ObjectBuilder\Test\Object\ComplexHierarchy;
use RstGroup\ObjectBuilder\Test\Object\EmptyConstructor;
use RstGroup\ObjectBuilder\Test\Object\MixedConstructor;
use RstGroup\ObjectBuilder\Test\Object\MixedConstructorWithDefaultValue;
use RstGroup\ObjectBuilder\Test\Object\NullableConstructor;
use RstGroup\ObjectBuilder\Test\Object\ScalarConstructor;
use RstGroup\ObjectBuilder\Test\Object\SecondEmptyConstructor;
use RstGroup\ObjectBuilder\Test\Object\WithoutConstructor;

abstract class BuilderTest extends TestCase
{
    /** @var Builder */
    protected static $builder;

    /** @test */
    public function iCanBuildObjectWithoutConstructor(): void
    {
        $class = WithoutConstructor::class;

        /** @var WithoutConstructor $object */
        $object = static::$builder->build($class, []);

        $this->assertInstanceOf(WithoutConstructor::class, $object);
    }

    /** @test */
    public function iCanBuildSimpleObjectWithScalarValuesInConstructor(): void
    {
        $data = [
            'some_string' => 'some string',
            'some_int' => 999,
        ];
        $class = ScalarConstructor::class;

        /** @var ScalarConstructor $object */
        $object = static::$builder->build($class, $data);

        $this->assertInstanceOf(ScalarConstructor::class, $object);
        $this->assertSame('some string', $object->someString);
        $this->assertSame(999, $object->someInt);
    }

    /** @test */
    public function iCanBuildSimpleObjectWithScalarAndObjectValuesInConstructor(): void
    {
        $data = [
            'some_string' => 'some string',
            'some_int' => 999,
            'some_object' => [],
        ];
        $class = MixedConstructor::class;

        /** @var MixedConstructor $object */
        $object = static::$builder->build($class, $data);

        $this->assertInstanceOf(MixedConstructor::class, $object);
        $this->assertSame('some string', $object->someString);
        $this->assertSame(999, $object->someInt);
        $this->assertInstanceOf(EmptyConstructor::class, $object->someObject);
    }

    /** @test */
    public function iCanBuildSimpleObjectWithDefaultValuesInConstructor(): void
    {
        $data = [
            'some_object' => [],
        ];
        $class = MixedConstructorWithDefaultValue::class;

        /** @var MixedConstructorWithDefaultValue $object */
        $object = static::$builder->build($class, $data);

        $this->assertInstanceOf(MixedConstructorWithDefaultValue::class, $object);
        $this->assertSame('some string', $object->someString);
        $this->assertSame(999, $object->someInt);
        $this->assertInstanceOf(EmptyConstructor::class, $object->someObject);
    }

    /** @test */
    public function iCanBuildObjectWithObjectCollectionWithoutUseInConstructor(): void
    {
        $data = [
            'list' => [
                [
                    'some_string' => 'some string1',
                    'some_int' => 1,
                ],
                [
                    'some_string' => 'some string2',
                    'some_int' => 2,
                ],
            ],
        ];
        $class = Collection\WithoutUseStmtConstructor::class;

        /** @var Collection\WithoutUseStmtConstructor $object */
        $object = static::$builder->build($class, $data);

        $this->assertInstanceOf(Collection\WithoutUseStmtConstructor::class, $object);
        $this->assertCount(2, $object->list);
        foreach ($object->list as $element) {
            $this->assertInstanceOf(Collection\ScalarConstructor::class, $element);
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
        $class = Collection\WithUseStmtConstructor::class;

        /** @var Collection\WithUseStmtConstructor $object */
        $object = static::$builder->build($class, $data);

        $this->assertInstanceOf(Collection\WithUseStmtConstructor::class, $object);
        $this->assertCount(2, $object->list);
        foreach ($object->list as $element) {
            $this->assertInstanceOf(SecondEmptyConstructor::class, $element);
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
        $class = Collection\WithoutUseButWithFQNTypedArrayConstructor::class;

        /** @var Collection\WithoutUseButWithFQNTypedArrayConstructor $object */
        $object = static::$builder->build($class, $data);

        $this->assertInstanceOf(Collection\WithoutUseButWithFQNTypedArrayConstructor::class, $object);
        $this->assertCount(2, $object->list);
        foreach ($object->list as $element) {
            $this->assertInstanceOf(EmptyConstructor::class, $element);
        }
    }

    /** @test */
    public function iCanBuildAdvancedObjectHierarchy(): void
    {
        $data = [
            'some_string' => 'some string3',
            'simple_object_1' => [
                'some_string' => 'some string1',
                'someInt' => 1,
            ],
            'simple_object_2' => [
                'some_string' => 'some string2',
                'some_int' => 2,
                'some_object' => [],
            ],
            'some_int' => 3,
        ];
        $class = ComplexHierarchy::class;

        /** @var ComplexHierarchy $object */
        $object = static::$builder->build($class, $data);

        $this->assertInstanceOf(ComplexHierarchy::class, $object);
        $this->assertSame('some string3', $object->someString);
        $this->assertSame(3, $object->someInt);
        $this->assertInstanceOf(ScalarConstructor::class, $object->simpleObject1);
        $this->assertInstanceOf(MixedConstructor::class, $object->simpleObject2);
        $this->assertSame(1, $object->simpleObject1->someInt);
        $this->assertSame('some string1', $object->simpleObject1->someString);
        $this->assertSame(2, $object->simpleObject2->someInt);
        $this->assertSame('some string2', $object->simpleObject2->someString);
    }

    /** @test */
    public function iCanBuildObjectWithScalarCollectionTypedArrayInConstructor(): void
    {
        $data = [
            'list1' => ['str', 'str'],
            'list2' => ['str', 'str'],
        ];
        $class = Collection\WithScalarTypedArrayConstructor::class;

        /** @var Collection\WithScalarTypedArrayConstructor $object */
        $object = static::$builder->build($class, $data);

        $this->assertInstanceOf(Collection\WithScalarTypedArrayConstructor::class, $object);
        $this->assertCount(2, $object->list1);
        $this->assertCount(2, $object->list2);
        foreach ($object->list1 as $element) {
            $this->assertSame('str', $element);
        }
        foreach ($object->list2 as $element) {
            $this->assertSame('str', $element);
        }
    }

    /** @test */
    public function iCanBuildObjectWithBothScalarAndObjectCollectionTypedArrayInConstructor(): void
    {
        $data = [
            'list1' => ['str', 'str'],
            'list2' => [
                [
                    'some_string' => 'some string1',
                    'some_int' => 1,
                ],
                [
                    'some_string' => 'some string2',
                    'some_int' => 2,
                ],
            ],
        ];
        $class = Collection\WithScalarTypedArrayAndObjectListConstructor::class;

        /** @var Collection\WithScalarTypedArrayAndObjectListConstructor $object */
        $object = static::$builder->build($class, $data);

        $this->assertInstanceOf(Collection\WithScalarTypedArrayAndObjectListConstructor::class, $object);
        $this->assertCount(2, $object->list1);
        $this->assertCount(2, $object->list2);
        foreach ($object->list1 as $element) {
            $this->assertSame('str', $element);
        }
        foreach ($object->list2 as $element) {
            $this->assertInstanceOf(Collection\ScalarConstructor::class, $element);
        }
    }

    /** @test */
    public function iCanBuildObjectWithNullableParameterWithoutDefaultValue(): void
    {
        $data = [
            'some_string_1' => 'some string1',
            'some_string_2' => 'some string2',
        ];
        $class = NullableConstructor::class;

        /** @var NullableConstructor $object */
        $object = static::$builder->build($class, $data);

        $this->assertInstanceOf(NullableConstructor::class, $object);
        $this->assertSame('some string1', $object->someString1);
        $this->assertSame('some string2', $object->someString2);
        $this->assertNull($object->someInt);
    }

    /** @test */
    public function iCanBuildObjectWithNullableParameterWithHimValueValue(): void
    {
        $data = [
            'some_string_1' => 'some string1',
            'some_int' => 123,
            'some_string_2' => 'some string2',
        ];
        $class = NullableConstructor::class;

        /** @var NullableConstructor $object */
        $object = static::$builder->build($class, $data);

        $this->assertInstanceOf(NullableConstructor::class, $object);
        $this->assertSame('some string1', $object->someString1);
        $this->assertSame('some string2', $object->someString2);
        $this->assertSame(123, $object->someInt);
    }
}
