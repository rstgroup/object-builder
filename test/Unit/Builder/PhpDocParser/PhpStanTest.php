<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Test\Unit\Builder\PhpDocParser;

use PhpParser\Lexer\Emulative;
use PhpParser\ParserFactory;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionParameter;
use RstGroup\ObjectBuilder\PhpDocParser\PhpStan;
use RstGroup\ObjectBuilder\Test\SomeObjectWithEmptyConstructor;


class PhpStanTest extends TestCase
{
    /** @var PhpStan */
    private static $parser;

    public static function setUpBeforeClass()
    {
        self::$parser = new PhpStan(
            new PhpDocParser(
                new TypeParser(),
                new ConstExprParser()
            ),
            (new ParserFactory())->create(ParserFactory::PREFER_PHP7, new Emulative([
                'usedAttributes' => ['comments', 'startLine', 'endLine', 'startFilePos', 'endFilePos'],
            ]))
        );
    }

    /**
     * @test
     * @dataProvider commentsWithObjectList
     */
    public function whenPhpDockContainListOfObjectThenReturnTrue(string $doc)
    {
        $containsListOfObjects = self::$parser->isListOfObject($doc, 'list');

        $this->assertTrue($containsListOfObjects);
    }

    /**
     * @test
     * @dataProvider commentsWithoutObjectList
     */
    public function whenPhpDockDoesNotContainListOfObjectThenReturnFalse(string $doc)
    {
        $containsListOfObjects = self::$parser->isListOfObject($doc, 'list');

        $this->assertFalse($containsListOfObjects);
    }

    /**
     * @test
     * @dataProvider commentsWithScalarList
     */
    public function whenPhpDockContainListOfScalarInsteadObjectThenReturnFalse(string $doc)
    {
        $containsListOfObjects = self::$parser->isListOfObject($doc, 'list');

        $this->assertFalse($containsListOfObjects);
    }

    /**
     * @test
     * @dataProvider commentsWithDifferentObjectListDeclaration
     */
    public function returnObjectClassOfObjectList(string $doc)
    {
        $class = self::$parser->getListType(
            $doc,
            new class extends ReflectionParameter {
                public function __construct() {}

                public function getName()
                {
                    return 'list';
                }

                public function getDeclaringClass()
                {
                    return new ReflectionClass(SomeObjectWithEmptyConstructor::class);
                }
            }
        );

        $this->assertSame(SomeObjectWithEmptyConstructor::class, $class);
    }

    /** @return string[][] */
    public function commentsWithObjectList(): array
    {
        return [
            'only param' => [
                '/** @param SimpleObject[] $list */',
            ],
            'multi params' => [
                '/** 
                  * @param string[] $strings
                  * @param int $int
                  * @param SimpleObject[] $list
                  * @param SimpleObjectTwo[] $collection
                  */',
            ],
            'param and return' => [
                '/** 
                  * @param SimpleObject[] $list
                  * @return SimpleObject[]
                  */',
            ],
        ];
    }

    /** @return string[][] */
    public function commentsWithoutObjectList(): array
    {
        return [
            'only return' => [
                '/** @return SimpleObject[] */',
            ],
            'multi params' => [
                '/** 
                  * @param string[] $strings
                  * @param int $int
                  * @param SimpleObjectTwo[] $collection
                  */',
            ],
            'param and return' => [
                '/** 
                  * @param SimpleObject[] $collection
                  * @return SimpleObject[]
                  */',
            ],
        ];
    }

    /** @return string[][] */
    public function commentsWithScalarList(): array
    {
        return [
            'string' => [
                '/** @param string[] $list */',
            ],
            'int' => [
                '/** @param int[] $list */',
            ],
            'bool' => [
                '/** @param bool[] $list */',
            ],
            'float' => [
                '/** @param float[] $list */',
            ],
            'double' => [
                '/** @param double[] $list */',
            ],
            'mixed' => [
                '/** @param mixed[] $list */',
            ],
        ];
    }

    /** @return string[][] */
    public function commentsWithDifferentObjectListDeclaration(): array
    {
        return [
            'FQCN' => [
                '/** @param '
                . SomeObjectWithEmptyConstructor::class
                . '[] $list */',
            ],
            'with use statement' => [
                '/** @param SomeObjectWithEmptyConstructor[] $list */',
            ],
            'without use statement in same namespace' => [
                '/** @param SomeObjectWithEmptyConstructor[] $list */',
            ],
//            TODO
//            'with partial use statement' => [
//                '/** @param Object\SomeObjectWithEmptyConstructor[] $list */',
//            ],
        ];
    }
}
