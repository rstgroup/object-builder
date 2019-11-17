<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Test\Unit\Builder\Blueprint\Factory\CodeGenerator\PatternGenerator;

use PHPUnit\Framework\TestCase;
use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Pattern\Generator\Dummy;
use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Pattern\Generator\StoreDecorator;
use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Pattern\Store\Memory;

class StoreDecoratorTest extends TestCase
{
    /** @test */
    public function returnPatternWhenExistInStore(): void
    {
        $patternGeneratorDecorator = new StoreDecorator(
            new Memory(['class' => 'pattern']),
            new Dummy()
        );

        $pattern = $patternGeneratorDecorator->create('class');

        $this->assertSame('pattern', $pattern);
    }

    /** @test */
    public function returnNewCreatedPatternWhenPatterDoesNotExistInMemory(): void
    {
        $patternGeneratorDecorator = new StoreDecorator(
            new Memory(),
            new Dummy(['class' => 'pattern'])
        );

        $pattern = $patternGeneratorDecorator->create('class');

        $this->assertSame('pattern', $pattern);
    }
}
