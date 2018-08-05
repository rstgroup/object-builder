<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Test\Unit\Builder\Blueprint\Factory\CodeGenerator\Store;

use PHPUnit\Framework\TestCase;
use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\PatternStore\Memory;

class InMemoryTest extends TestCase
{
    /** @test */
    public function iCanSaveBlueprintInMemory(): void
    {
        $store = new Memory();

        $store->save('SomeClass', 'blueprint');

        $this->assertSame('blueprint', $store->store()['SomeClass']);
    }

    /** @test */
    public function iCanGetSavedBlueprintInMemory(): void
    {
        $store = new Memory([
            'SomeClass' => 'return function() { return \'some string\'; };',
        ]);

        $function = $store->get('SomeClass');

        $this->assertSame(
            'return function() { return \'some string\'; };',
            $function
        );
    }

    /** @test */
    public function whenFileExistsInMemoryThenOverrideIt(): void
    {
        $store = new Memory([
            'SomeClass' => 'some string',
        ]);

        $store->save('SomeClass', 'override');

        $memoryStore = $store->store();
        $this->assertSame('override', reset($memoryStore));
    }

    /** @test */
    public function whenFileDoesNotExistInMemoryThenReturnNull(): void
    {
        $store = new Memory();

        $function = $store->get('SomeClass');

        $this->assertNull($function);
    }
}
