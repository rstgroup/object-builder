<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Test\Integration\Builder\Blueprint\Factory\CodeGenerator\Store;

use PHPUnit\Framework\TestCase;
use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\PatternStore\Filesystem;

class FilesystemTest extends TestCase
{
    public function setUp(): void
    {
        if (!file_exists('/tmp/SomeClass')) {
            return;
        }

        unlink('/tmp/SomeClass');
    }

    /** @test */
    public function iCanSaveBlueprintInFilesystem(): void
    {
        $store = new Filesystem('/tmp/');

        $store->save('SomeClass', 'content php');

        $savedBlueprint = file_get_contents('/tmp/SomeClass');
        $this->assertSame('content php', $savedBlueprint);
    }

    /** @test */
    public function iCanGetSavedBlueprintInFilesystem(): void
    {
        $store = new Filesystem('/tmp/');
        file_put_contents(
            '/tmp/SomeClass',
            'return function() { return \'some string\'; };'
        );

        $function = $store->get('SomeClass');

        $this->assertSame(
            'return function() { return \'some string\'; };',
            $function
        );
    }

    /** @test */
    public function whenFileExistsInFilesystemThenOverrideIt(): void
    {
        $store = new Filesystem('/tmp/');
        file_put_contents(
            '/tmp/SomeClass',
            'some string'
        );

        $store->save('SomeClass', 'override');

        $savedBlueprint = file_get_contents('/tmp/SomeClass');
        $this->assertSame('override', $savedBlueprint);
    }

    /** @test */
    public function whenFileDoesNotExistInFilesystemThenReturnNull(): void
    {
        $store = new Filesystem('/tmp/');

        $function = $store->get('SomeClass');

        $this->assertNull($function);
    }
}
