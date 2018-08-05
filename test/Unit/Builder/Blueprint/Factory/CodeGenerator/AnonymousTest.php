<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Test\Unit\Builder\Blueprint\Factory\CodeGenerator;

use PhpParser\Lexer\Emulative;
use PhpParser\ParserFactory;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPUnit\Framework\TestCase;
use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Node\Serializer\ArrayAccess;
use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\PatternGenerator\Anonymous;
use RstGroup\ObjectBuilder\PhpDocParser\PhpStan;
use RstGroup\ObjectBuilder\Test\Object\Collection\WithoutUseStmtConstructor;
use RstGroup\ObjectBuilder\Test\Object\ComplexHierarchy;
use RstGroup\ObjectBuilder\Test\Object\EmptyConstructor;
use RstGroup\ObjectBuilder\Test\Object\MixedConstructorWithDefaultValue;
use RstGroup\ObjectBuilder\Test\Object\ScalarConstructor;

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
        $class = EmptyConstructor::class;

        $blueprint = self::$factory->create($class);

        $this->assertSame(
            'return function(array $data) use ($class): object {

    return new RstGroup\ObjectBuilder\Test\Object\EmptyConstructor();
};',
            $blueprint
        );
    }

    /** @test */
    public function iCanBuildSimpleObjectWithScalarValuesInConstructor(): void
    {
        $class = ScalarConstructor::class;

        $blueprint = self::$factory->create($class);

// @codingStandardsIgnoreStart
        $this->assertSame(
            'return function(array $data) use ($class): object {

    return new RstGroup\ObjectBuilder\Test\Object\ScalarConstructor($data[\'someString\'], $data[\'someInt\']);
};',
            $blueprint
        );
// @codingStandardsIgnoreEnd
    }

    /** @test */
    public function iCanBuildSimpleObjectWithDefaultValuesInConstructor(): void
    {
        $class = MixedConstructorWithDefaultValue::class;

        $blueprint = self::$factory->create($class);

// @codingStandardsIgnoreStart
        $this->assertSame(
            'return function(array $data) use ($class): object {
    $default = array (
  \'someString\' => \'some string\',
  \'someInt\' => 999,
);
    $data = array_merge($default, $data);

    return new RstGroup\ObjectBuilder\Test\Object\MixedConstructorWithDefaultValue(new RstGroup\ObjectBuilder\Test\Object\EmptyConstructor(), $data[\'someString\'], $data[\'someInt\']);
};',
            $blueprint
        );
// @codingStandardsIgnoreEnd
    }

    /** @test */
    public function iCanBuildAdvancedObjectHierarchy(): void
    {
        $class = ComplexHierarchy::class;

        $blueprint = self::$factory->create($class);

// @codingStandardsIgnoreStart
        $this->assertSame(
            'return function(array $data) use ($class): object {

    return new RstGroup\ObjectBuilder\Test\Object\ComplexHierarchy($data[\'someString\'], new RstGroup\ObjectBuilder\Test\Object\ScalarConstructor($data[\'simpleObject1\'][\'someString\'], $data[\'simpleObject1\'][\'someInt\']), new RstGroup\ObjectBuilder\Test\Object\MixedConstructor($data[\'simpleObject2\'][\'someString\'], $data[\'simpleObject2\'][\'someInt\'], new RstGroup\ObjectBuilder\Test\Object\EmptyConstructor()), $data[\'someInt\']);
};',
            $blueprint
        );
// @codingStandardsIgnoreEnd
    }

    /** @test */
    public function iCanBuildObjectWithObjectCollectionWithoutUseInConstructor(): void
    {
        $class = WithoutUseStmtConstructor::class;

        $blueprint = self::$factory->create($class);

// @codingStandardsIgnoreStart
        $this->assertSame('return function(array $data) use ($class): object {

    return new RstGroup\ObjectBuilder\Test\Object\Collection\WithoutUseStmtConstructor((function (array $list) {
        $arr = [];
        foreach ($list as $data) {
            $arr[] = new RstGroup\ObjectBuilder\Test\Object\Collection\ScalarConstructor($data[\'someString\'], $data[\'someInt\']);
        }
        
        return $arr;
    })($data[\'list\']));
};',
            $blueprint
        );
// @codingStandardsIgnoreEnd
    }
}
