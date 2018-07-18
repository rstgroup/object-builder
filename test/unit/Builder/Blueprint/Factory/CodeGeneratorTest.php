<?php declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Test\unit\Builder\Blueprint\Factory;

use PHPUnit\Framework\TestCase;
use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator;
use RstGroup\ObjectBuilder\Test\SomeObjectWithEmptyConstructor;

class CodeGeneratorTest extends TestCase
{
    /** @test */
    public function iCanGenerateSimpleObjectClosure(): void
    {
        $factory = new CodeGenerator();
        $class = SomeObjectWithEmptyConstructor::class;

        $closure = $factory->create($class);

        $this->assertInstanceOf($class, $closure([]));
    }
}
