<?php

declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Test\unit\Builder\ParameterNameStrategy;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RstGroup\ObjectBuilder\Builder\ParameterNameStrategy\SnakeCase;

final class SnakeCaseTest extends TestCase
{
    private SnakeCase $strategy;

    public function setUp(): void
    {
        $this->strategy = new SnakeCase();
    }

    #[DataProvider('validStrings')]
    #[Test]
    public function snakeCaseStrategyReturnTrueForStringsOnlyInSneakCaseFormat(string $string): void
    {
        $isFulfilled = $this->strategy->isFulfilled($string);

        $this->assertTrue($isFulfilled);
    }

    #[DataProvider('invalidStrings')]
    #[Test]
    public function snakeCaseStrategyReturnFalseForStringsWitUnderscoreOrSpace(string $string): void
    {
        $isFulfilled = $this->strategy->isFulfilled($string);

        $this->assertFalse($isFulfilled);
    }

    #[Test]
    public function snakeCaseStrategyReturnGivenParameterAsCamelCase(): void
    {
        $string = $this->strategy->getName('simple_snake_case');

        $this->assertSame('simpleSnakeCase', $string);
    }

    public static function validStrings(): Iterator
    {
        yield 'simple string' => ['string'];
        yield 'sneak case string' => ['valid_string'];
        yield 'sneak case string with number' => ['valid_string_1'];
    }

    public static function invalidStrings(): Iterator
    {
        yield 'string with space' => ['string string'];
        yield 'string with minus' => ['string-string'];
    }
}
