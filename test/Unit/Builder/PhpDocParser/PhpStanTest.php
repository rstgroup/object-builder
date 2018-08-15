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
use RstGroup\ObjectBuilder\BuildingError;
use RstGroup\ObjectBuilder\PhpDocParser\PhpStan;
use RstGroup\ObjectBuilder\Test\Object\Collection\WithoutUseButWithFQNTypedArrayConstructor;
use RstGroup\ObjectBuilder\Test\Object\Collection\WithoutUseStmtConstructor;
use RstGroup\ObjectBuilder\Test\Object\Collection\WithUseStmtConstructor;

class PhpStanTest extends TestCase
{
    /** @var PhpStan */
    private static $parser;

    public static function setUpBeforeClass(): void
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
    public function whenPhpDockContainListOfObjectThenReturnTrue(string $doc): void
    {
        $containsListOfObjects = self::$parser->isListOfObject($doc, 'list');

        $this->assertTrue($containsListOfObjects);
    }

    /**
     * @test
     * @dataProvider commentsWithoutObjectList
     */
    public function whenPhpDockDoesNotContainListOfObjectThenReturnFalse(string $doc): void
    {
        $containsListOfObjects = self::$parser->isListOfObject($doc, 'list');

        $this->assertFalse($containsListOfObjects);
    }

    /**
     * @test
     * @dataProvider commentsWithScalarList
     */
    public function whenPhpDockContainListOfScalarInsteadObjectThenReturnFalse(string $doc): void
    {
        $containsListOfObjects = self::$parser->isListOfObject($doc, 'list');

        $this->assertFalse($containsListOfObjects);
    }

    /**
     * @test
     * @dataProvider commentsWithDifferentObjectListDeclaration
     */
    public function returnObjectClassOfObjectList(
        string $doc,
        ReflectionParameter $param,
        string $expectedClass
    ): void {
        $class = self::$parser->getListType($doc, $param);

        $this->assertSame($expectedClass, $class);
    }

    /** @test */
    public function throwExceptionWhenParameterIsNotDeclaredInPhpDoc(): void
    {
        $this->expectException(BuildingError::class);
        $paramReflection = new class extends ReflectionParameter
        {
            public function __construct()
            {
            }

            public function getName(): string
            {
                return 'unexistedName';
            }
        };

        self::$parser->getListType(
            '/** @param SimpleObject[] $list */',
            $paramReflection
        );
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

    /** @return mixed[][] */
    public function commentsWithDifferentObjectListDeclaration(): array
    {
        $constructors = [
            'FQCN' => (new ReflectionClass(
                WithoutUseButWithFQNTypedArrayConstructor::class
            ))->getConstructor(),
            'with use statement' => (new ReflectionClass(
                WithUseStmtConstructor::class
            ))->getConstructor(),
            'without use statement in same namespace' => (new ReflectionClass(
                WithoutUseStmtConstructor::class
            ))->getConstructor(),
        ];

        return [
            'FQCN' => [
                $constructors['FQCN']->getDocComment(),
                $constructors['FQCN']->getParameters()[0],
                '\RstGroup\ObjectBuilder\Test\Object\EmptyConstructor',
            ],
            'with use statement' => [
                $constructors['with use statement']->getDocComment(),
                $constructors['with use statement']->getParameters()[0],
                '\RstGroup\ObjectBuilder\Test\Object\SecondEmptyConstructor',
            ],
            'without use statement in same namespace' => [
                $constructors['without use statement in same namespace']->getDocComment(),
                $constructors['without use statement in same namespace']->getParameters()[0],
                '\RstGroup\ObjectBuilder\Test\Object\Collection\ScalarConstructor',
            ],
//            TODO
//            'partial namespace with use statement' => [
//            ],
//            'partial namespace without use statement' => [
//            ],
        ];
    }
}
