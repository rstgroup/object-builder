<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Test\Unit\Builder\Blueprint\Factory\CodeGenerator\Node\Serializer;

use PHPUnit\Framework\TestCase;
use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Node;
use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Node\Complex;
use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Node\ObjectList;
use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Node\Scalar;
use RstGroup\ObjectBuilder\Builder\Blueprint\Factory\CodeGenerator\Node\Serializer\ArrayAccess;
use RstGroup\ObjectBuilder\BuildingError;

class ArrayAccessTest extends TestCase
{
    /** @var ArrayAccess */
    private static $serializer;

    public static function setUpBeforeClass(): void
    {
        static::$serializer = new ArrayAccess();
    }

    /** @test */
    public function serializeScalarNodeToArrayAccessStringWithNodeName(): void
    {
        $node = new Scalar('string', 'someName');

        $serializedNode = static::$serializer->serialize($node);

        $this->assertSame('[\'someName\']', $serializedNode);
    }

    /** @test */
    public function serializeComplexNodeToArrayAccessString(): void
    {
        $node = new Complex('SomeClassName', 'someName');
        $scalarStringNode = new Scalar('string', 'someStringName');
        $scalarIntNode = new Scalar('int', 'someInt');
        $node->add($scalarStringNode);
        $node->add($scalarIntNode);

        $serializedNode = static::$serializer->serialize($node);

        $this->assertSame(
            'new SomeClassName($data[\'someName\'][\'someStringName\'], $data[\'someName\'][\'someInt\'])',
            $serializedNode
        );
    }

    /** @test */
    public function serializeObjectListNodeToArrayAccessString(): void
    {
        $objectNode = new Complex('SomeClassName', 'someName');
        $scalarStringNode = new Scalar('string', 'someStringName');
        $scalarIntNode = new Scalar('int', 'someInt');
        $objectNode->add($scalarStringNode);
        $objectNode->add($scalarIntNode);
        $node = new ObjectList('someList', $objectNode);

        $serializedNode = static::$serializer->serialize($node);

        $this->assertSame(
            '(function (array $list) {
        $arr = [];
        foreach ($list as $data) {
            $arr[] = new SomeClassName($data[\'someName\'][\'someStringName\'], $data[\'someName\'][\'someInt\']);
        }
        
        return $arr;
    })($data[\'someList\'])',
            $serializedNode
        );
    }

    /** @test */
    public function whenNodeObjectIsNotKnowThenThrowsException(): void
    {
        $node = new class('a', 'a') extends Node {};

        $this->expectException(BuildingError::class);

        static::$serializer->serialize($node);
    }
}
