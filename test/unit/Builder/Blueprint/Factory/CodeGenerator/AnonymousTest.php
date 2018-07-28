<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Test\unit\Builder\Blueprint\Factory\CodeGenerator;

use PhpParser\Lexer\Emulative;
use PhpParser\ParserFactory;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPUnit\Framework\TestCase;
use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Node\Serializer\ArrayAccess;
use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\PatternGenerator\Anonymous;
use RstGroup\ObjectBuilder\PhpDocParser\PhpStan;
use RstGroup\ObjectBuilder\Test\ListOfObjectsWithoutUseStmtConstructor;
use RstGroup\ObjectBuilder\Test\SimpleMixedConstructorWithDefaultValue;
use RstGroup\ObjectBuilder\Test\SimpleScalarConstructor;
use RstGroup\ObjectBuilder\Test\SomeAggregateRoot;
use RstGroup\ObjectBuilder\Test\SomeObjectWithEmptyConstructor;

class AnonymousTest extends TestCase
{
    /** @var Anonymous */
    private static $factory;

    public static function setUpBeforeClass(): void
    {
        self::$factory = new Anonymous(
            new PhpStan(
                new PhpDocParser(
                    new TypeParser(),
                    new ConstExprParser()
                ),
                (new ParserFactory())->create(ParserFactory::PREFER_PHP7, new Emulative([
                    'usedAttributes' => ['comments', 'startLine', 'endLine', 'startFilePos', 'endFilePos'],
                ]))
            ),
            new ArrayAccess()
        );
    }

    /** @test */
    public function iCanGenerateSimpleObjectClosure(): void
    {
        $class = SomeObjectWithEmptyConstructor::class;

        $blueprint = self::$factory->create($class);

        $this->assertSame(
            'return function(array $data) use ($class): object {

    return new RstGroup\ObjectBuilder\Test\SomeObjectWithEmptyConstructor();
};',
            $blueprint
        );
    }

    /** @test */
    public function iCanBuildSimpleObjectWithScalarValuesInConstructor(): void
    {
        $class = SimpleScalarConstructor::class;

        $blueprint = self::$factory->create($class);

// @codingStandardsIgnoreStart
        $this->assertSame(
            'return function(array $data) use ($class): object {

    return new RstGroup\ObjectBuilder\Test\SimpleScalarConstructor($data[\'someString\'], $data[\'someInt\']);
};',
            $blueprint
        );
// @codingStandardsIgnoreEnd
    }

    /** @test */
    public function iCanBuildSimpleObjectWithDefaultValuesInConstructor(): void
    {
        $class = SimpleMixedConstructorWithDefaultValue::class;

        $blueprint = self::$factory->create($class);

// @codingStandardsIgnoreStart
        $this->assertSame(
            'return function(array $data) use ($class): object {
    $default = array (
  \'someString\' => \'some string\',
  \'someInt\' => 999,
);
    $data = array_merge($default, $data);

    return new RstGroup\ObjectBuilder\Test\SimpleMixedConstructorWithDefaultValue(new RstGroup\ObjectBuilder\Test\SomeObjectWithEmptyConstructor(), $data[\'someString\'], $data[\'someInt\']);
};',
            $blueprint
        );
// @codingStandardsIgnoreEnd
    }

    /** @test */
    public function iCanBuildAdvancedObjectHierarchy(): void
    {
        $class = SomeAggregateRoot::class;

        $blueprint = self::$factory->create($class);

// @codingStandardsIgnoreStart
        $this->assertSame(
            'return function(array $data) use ($class): object {

    return new RstGroup\ObjectBuilder\Test\SomeAggregateRoot($data[\'someString\'], new RstGroup\ObjectBuilder\Test\SimpleScalarConstructor($data[\'simpleObject1\'][\'someString\'], $data[\'simpleObject1\'][\'someInt\']), new RstGroup\ObjectBuilder\Test\SimpleMixedConstructor($data[\'simpleObject2\'][\'someString\'], $data[\'simpleObject2\'][\'someInt\'], new RstGroup\ObjectBuilder\Test\SomeObjectWithEmptyConstructor()), $data[\'someInt\']);
};',
            $blueprint
        );
// @codingStandardsIgnoreEnd
    }

    /** @test */
    public function iCanBuildObjectWithObjectCollectionWithoutUseInConstructor(): void
    {
        $class = ListOfObjectsWithoutUseStmtConstructor::class;

        $blueprint = self::$factory->create($class);

// @codingStandardsIgnoreStart
        $this->assertSame('return function(array $data) use ($class): object {

    return new RstGroup\ObjectBuilder\Test\ListOfObjectsWithoutUseStmtConstructor((function (array $list) {
        $arr = [];
        foreach ($list as $data) {
            $arr[] = new RstGroup\ObjectBuilder\Test\SimpleScalarConstructor($data[\'someString\'], $data[\'someInt\']);
        }
        
        return $arr;
    })($data[\'list\']));
};',
            $blueprint
        );
// @codingStandardsIgnoreEnd
    }
}
