<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Test\unit\Builder\Blueprint\Factory\CodeGenerator;

use PHPUnit\Framework\TestCase;
use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Anonymous;
use RstGroup\ObjectBuilder\Test\ListOfObjectsWithoutUseStmtConstructor;
use RstGroup\ObjectBuilder\Test\SimpleMixedConstructorWithDefaultValue;
use RstGroup\ObjectBuilder\Test\SimpleScalarConstructor;
use RstGroup\ObjectBuilder\Test\SomeAggregateRoot;
use RstGroup\ObjectBuilder\Test\SomeObjectWithEmptyConstructor;

class AnonymousTest extends TestCase
{
    /** @test */
    public function iCanGenerateSimpleObjectClosure(): void
    {
        $factory = new Anonymous();
        $class = SomeObjectWithEmptyConstructor::class;

        $blueprint = $factory->create($class);

        $this->assertSame(
            '<?php

return function(array $data) use ($class): object {

    return new RstGroup\ObjectBuilder\Test\SomeObjectWithEmptyConstructor();
}',
            $blueprint
        );
    }

    /** @test */
    public function iCanBuildSimpleObjectWithScalarValuesInConstructor(): void
    {
        $factory = new Anonymous();
        $class = SimpleScalarConstructor::class;

        $blueprint = $factory->create($class);

        $this->assertSame(
            '<?php

return function(array $data) use ($class): object {

    return new RstGroup\ObjectBuilder\Test\SimpleScalarConstructor($data[\'someString\'], $data[\'someInt\']);
}',
            $blueprint
        );
    }

    /** @test */
    public function iCanBuildSimpleObjectWithDefaultValuesInConstructor(): void
    {
        $factory = new Anonymous();
        $class = SimpleMixedConstructorWithDefaultValue::class;

        $blueprint = $factory->create($class);

        $this->assertSame(
            '<?php

return function(array $data) use ($class): object {
    $default = array (
  \'someString\' => \'some string\',
  \'someInt\' => 999,
);
    $data = array_merge($default, $data);

    return new RstGroup\ObjectBuilder\Test\SimpleMixedConstructorWithDefaultValue(new RstGroup\ObjectBuilder\Test\SomeObjectWithEmptyConstructor(), $data[\'someString\'], $data[\'someInt\']);
}',
            $blueprint
        );
    }

    /** @test */
    public function iCanBuildAdvancedObjectHierarchy(): void
    {
        $factory = new Anonymous();
        $class = SomeAggregateRoot::class;

        $blueprint = $factory->create($class);

        $this->assertSame(
            '<?php

return function(array $data) use ($class): object {

    return new RstGroup\ObjectBuilder\Test\SomeAggregateRoot($data[\'someString\'], new RstGroup\ObjectBuilder\Test\SimpleScalarConstructor($data[\'simpleObject1\'][\'someString\'], $data[\'simpleObject1\'][\'someInt\']), new RstGroup\ObjectBuilder\Test\SimpleMixedConstructor($data[\'simpleObject2\'][\'someString\'], $data[\'simpleObject2\'][\'someInt\'], new RstGroup\ObjectBuilder\Test\SomeObjectWithEmptyConstructor()), $data[\'someInt\']);
}',
            $blueprint
        );
    }

    /** @test */
    public function iCanBuildObjectWithObjectCollectionWithoutUseInConstructor(): void
    {
        $factory = new Anonymous();
        $class = ListOfObjectsWithoutUseStmtConstructor::class;

        $blueprint = $factory->create($class);

        $this->assertSame('<?php

return function(array $data) use ($class): object {

    return new RstGroup\ObjectBuilder\Test\ListOfObjectsWithoutUseStmtConstructor((function (array $list) {
            $arr = [];
            foreach ($list as $data) {
                $arr[] = new RstGroup\ObjectBuilder\Test\SimpleScalarConstructor($data[\'someString\'], $data[\'someInt\']);
            }
            
            return $arr;
        })($data[\'list\']));
}', $blueprint);
    }
}
