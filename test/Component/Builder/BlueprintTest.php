<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Test\Component\Builder;

use PhpParser\Lexer\Emulative;
use PhpParser\ParserFactory;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TypeParser;
use RstGroup\ObjectBuilder\Builder\Blueprint;
use RstGroup\ObjectBuilder\PhpDocParser\PhpStan;

class BlueprintTest extends BuilderTest
{
    public static function setUpBeforeClass(): void
    {
        static::$builder = new Blueprint(
            new Blueprint\Factory\CodeGenerator(
                new Blueprint\Factory\CodeGenerator\PatternGenerator\Anonymous(
                    new PhpStan(
                        new PhpDocParser(
                            new TypeParser(),
                            new ConstExprParser()
                        ),
                        (new ParserFactory())->create(ParserFactory::PREFER_PHP7, new Emulative([
                            'usedAttributes' => ['comments', 'startLine', 'endLine', 'startFilePos', 'endFilePos'],
                        ]))
                    ),
                    new Blueprint\Factory\CodeGenerator\Node\Serializer\ArrayAccess()
                )
            )
        );
    }
}
