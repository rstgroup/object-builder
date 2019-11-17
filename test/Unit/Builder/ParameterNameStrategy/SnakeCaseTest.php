<?php declare(strict_types = 1);

namespace RstGroup\ObjectBuilder\Test\Unit\Builder\ParameterNameStrategy;

use PHPUnit\Framework\TestCase;
use RstGroup\ObjectBuilder\Builder\ParameterNameStrategy\SnakeCase;

class SnakeCaseTest extends TestCase
{
    /** @var SnakeCase */
    private static $strategy;

    public static function setUpBeforeClass(): void
    {
        static::$strategy = new SnakeCase();
    }

    /**
     * @test
     * @dataProvider validStrings
     */
    public function snakeCaseStrategyReturnTrueForStringsOnlyInSneakCaseFormat(string $string): void
    {
        $isFulfilled = static::$strategy->isFulfilled($string);

        $this->assertTrue($isFulfilled);
    }

    /**
     * @test
     * @dataProvider invalidStrings
     */
    public function snakeCaseStrategyReturnFalseForStringsWitUnderscoreOrSpace(string $string): void
    {
        $isFulfilled = static::$strategy->isFulfilled($string);

        $this->assertFalse($isFulfilled);
    }

    /** @test */
    public function snakeCaseStrategyReturnGivenParameterAsCamelCase(): void
    {
        $string = static::$strategy->getName('simple_snake_case');

        $this->assertSame('simpleSnakeCase', $string);
    }

    /** @return string[][] */
    public function validStrings(): array
    {
        return [
            'simple string' => ['string'],
            'sneak case string' => ['valid_string'],
            'sneak case string with number' => ['valid_string_1'],
        ];
    }

    /** @return string[][] */
    public function invalidStrings(): array
    {
        return [
            'string with space' => ['string string'],
            'string with minus' => ['string-string'],
        ];
    }
}
