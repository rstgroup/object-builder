<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Test\Unit\Builder\ParameterNameStrategy;

use PHPUnit\Framework\TestCase;
use RstGroup\ObjectBuilder\Builder\ParameterNameStrategy\Simple;

class SimpleTest extends TestCase
{
    /** @var Simple */
    private static $strategy;

    public static function setUpBeforeClass(): void
    {
        static::$strategy = new Simple();
    }

    /**
     * @test
     * @dataProvider validStrings
     */
    public function simpleStrategyReturnTrueForStringsWithoutUnderscoreMinusAndSpace(string $string): void
    {
        $isFulfilled = static::$strategy->isFulfilled($string);

        $this->assertTrue($isFulfilled);
    }

    /**
     * @test
     * @dataProvider invalidStrings
     */
    public function simpleStrategyReturnFalseForStringsWitUnderscoreOrMinusOrSpace(string $string): void
    {
        $isFulfilled = static::$strategy->isFulfilled($string);

        $this->assertFalse($isFulfilled);
    }

    /** @test */
    public function simpleStrategyReturnGivenParameterWithoutModification(): void
    {
        $string = static::$strategy->getName('simpleCamelCase');

        $this->assertSame('simpleCamelCase', $string);
    }

    /** @return string[][] */
    public function validStrings(): array
    {
        return [
            'simple string' => ['string'],
            'camelCase string' => ['validString'],
            'string with number' => ['valid1'],
            'camelCase with number' => ['validString123String'],
        ];
    }

    /** @return string[][] */
    public function invalidStrings(): array
    {
        return [
            'string with space' => ['string string'],
            'string with underscore' => ['string_string'],
            'string with minus' => ['string-string'],
        ];
    }
}
