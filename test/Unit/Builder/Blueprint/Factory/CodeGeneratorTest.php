<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Test\Unit\Builder\Blueprint\Factory;

use PHPUnit\Framework\TestCase;
use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator;
use RstGroup\ObjectBuilder\BuildingError;

class CodeGeneratorTest extends TestCase
{
    /** @test */
    public function whenCreatedBlueprintIsNotCallableThenThrowBuildingError(): void
    {
        $codeGenerator = new CodeGenerator(
            new CodeGenerator\PatternGenerator\Dummy([
                'someClass' => 'return 123;',
            ])
        );

        $this->expectException(BuildingError::class);

        $codeGenerator->create('someClass');
    }

    /** @test */
    public function whenCreatedBlueprintIsCallableThenReturnIt(): void
    {
        $codeGenerator = new CodeGenerator(
            new CodeGenerator\PatternGenerator\Dummy([
                'someClass' => 'return function() { return 123; };',
            ])
        );

        $blueprint = $codeGenerator->create('someClass');

        $this->assertSame(123, $blueprint());
    }
}
