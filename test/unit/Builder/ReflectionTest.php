<?php

declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Test\unit\Builder;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RstGroup\ObjectBuilder\Builder\ParameterNameStrategy\Simple;
use RstGroup\ObjectBuilder\Builder\Reflection;
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

final class ReflectionTest extends TestCase
{
    private Reflection $builder;

    public function setUp(): void
    {
        $this->builder = new Reflection(new Simple());
    }

    #[Test]
    public function iCanBuildSimpleObjectWithScalarValuesInConstructor(): void
    {
        $data = [
            'someString' => 'some string',
            'someInt' => 999,
        ];
        $class = SimpleScalarConstructor::class;

        /** @var SimpleScalarConstructor $object */
        $object = $this->builder->build($class, $data);

        $this->assertInstanceOf(SimpleScalarConstructor::class, $object);
        $this->assertSame('some string', $object->someString);
        $this->assertSame(999, $object->someInt);
    }

    #[Test]
    public function iCanBuildSimpleObjectWithScalarAndObjectValuesInConstructor(): void
    {
        $data = [
            'someString' => 'some string',
            'someInt' => 999,
            'someObject' => [],
        ];
        $class = SimpleMixedConstructor::class;

        /** @var SimpleMixedConstructor $object */
        $object = $this->builder->build($class, $data);

        $this->assertInstanceOf(SimpleMixedConstructor::class, $object);
        $this->assertSame('some string', $object->someString);
        $this->assertSame(999, $object->someInt);
        $this->assertInstanceOf(SomeObjectWithEmptyConstructor::class, $object->someObject);
    }

    #[Test]
    public function iCanBuildSimpleObjectWithDefaultValuesInConstructor(): void
    {
        $data = [
            'someObject' => [],
        ];
        $class = SimpleMixedConstructorWithDefaultValue::class;

        /** @var SimpleMixedConstructorWithDefaultValue $object */
        $object = $this->builder->build($class, $data);

        $this->assertInstanceOf(SimpleMixedConstructorWithDefaultValue::class, $object);
        $this->assertSame('some string', $object->someString);
        $this->assertSame(999, $object->someInt);
        $this->assertInstanceOf(SomeObjectWithEmptyConstructor::class, $object->someObject);
    }

    #[Test]
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
        $object = $this->builder->build($class, $data);

        $this->assertInstanceOf(ListOfObjectsWithoutUseStmtConstructor::class, $object);
        $this->assertCount(2, $object->list);
        $this->assertContainsOnlyInstancesOf(SimpleScalarConstructor::class, $object->list);
    }

    #[Test]
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
        $object = $this->builder->build($class, $data);

        $this->assertInstanceOf(ListOfObjectsWithUseStmtConstructor::class, $object);
        $this->assertCount(2, $object->list);
        $this->assertContainsOnlyInstancesOf(SomeSecondObject::class, $object->list);
    }

    #[Test]
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
        $object = $this->builder->build($class, $data);

        $this->assertInstanceOf(ListOfObjectsWithoutUseButWithFQNTypedArrayConstructor::class, $object);
        $this->assertCount(2, $object->list);
        $this->assertContainsOnlyInstancesOf(SomeObject::class, $object->list);
    }

    #[Test]
    public function iCanBuildObjectWithScalarCollectionTypedArrayInConstructor(): void
    {
        $data = [
            'list1' => ['str', 'str'],
            'list2' => ['str', 'str'],
        ];
        $class = ListOfObjectsWithScalarTypedArrayConstructor::class;

        /** @var ListOfObjectsWithScalarTypedArrayConstructor $object */
        $object = $this->builder->build($class, $data);

        $this->assertInstanceOf(ListOfObjectsWithScalarTypedArrayConstructor::class, $object);
        $this->assertCount(2, $object->list1);
        $this->assertCount(2, $object->list2);
        foreach ($object->list1 as $element) {
            $this->assertSame('str', $element);
        }

        foreach ($object->list2 as $element) {
            $this->assertSame('str', $element);
        }
    }

    #[Test]
    public function iCanBuildObjectWithBothScalarAndObjectCollectionTypedArrayInConstructor(): void
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
        $object = $this->builder->build($class, $data);

        $this->assertInstanceOf(ListOfObjectsWithScalarTypedArrayAndObjectListConstructor::class, $object);
        $this->assertCount(2, $object->list1);
        $this->assertCount(2, $object->list2);
        foreach ($object->list1 as $element) {
            $this->assertSame('str', $element);
        }

        $this->assertContainsOnlyInstancesOf(SimpleScalarConstructor::class, $object->list2);
    }

    #[Test]
    public function iCanBuildAdvancedObjectHierarchy(): void
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
        $object = $this->builder->build($class, $data);

        $this->assertInstanceOf(SomeAggregateRoot::class, $object);
        $this->assertSame('some string', $object->someString);
        $this->assertSame(3, $object->someInt);
        $this->assertInstanceOf(SimpleScalarConstructor::class, $object->simpleObject1);
        $this->assertInstanceOf(SimpleMixedConstructor::class, $object->simpleObject2);
        $this->assertSame(1, $object->simpleObject1->someInt);
        $this->assertSame(2, $object->simpleObject2->someInt);
    }

    #[Test]
    public function iCanBuildObjectWithNullableParameterWithoutDefaultValue(): void
    {
        $data = [
            'someString1' => 'some string1',
            'someString2' => 'some string2',
        ];
        $class = SimpleNullableConstructor::class;

        /** @var SimpleNullableConstructor $object */
        $object = $this->builder->build($class, $data);

        $this->assertInstanceOf(SimpleNullableConstructor::class, $object);
        $this->assertSame('some string1', $object->someString1);
        $this->assertSame('some string2', $object->someString2);
        $this->assertNull($object->someInt);
    }

    #[Test]
    public function iCanBuildObjectWithNullableParameterWithHimValueValue(): void
    {
        $data = [
            'someString1' => 'some string1',
            'someInt' => 123,
            'someString2' => 'some string2',
        ];
        $class = SimpleNullableConstructor::class;

        /** @var SimpleNullableConstructor $object */
        $object = $this->builder->build($class, $data);

        $this->assertInstanceOf(SimpleNullableConstructor::class, $object);
        $this->assertSame('some string1', $object->someString1);
        $this->assertSame('some string2', $object->someString2);
        $this->assertSame(123, $object->someInt);
    }
}
