<?php

declare(strict_types=1);

namespace RstGroup\ObjectBuilder\Test\unit\Builder\ParameterNameStrategy;

use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RstGroup\ObjectBuilder\Builder\ParameterNameStrategy\Simple;

final class SimpleTest extends TestCase
{
    private Simple $strategy;

    public function setUp(): void
    {
        $this->strategy = new Simple();
    }

    #[DataProvider('validStrings')]
    #[Test]
    public function simpleStrategyReturnTrueForStringsWithoutUnderscoreMinusAndSpace(string $string): void
    {
        $isFulfilled = $this->strategy->isFulfilled($string);

        $this->assertTrue($isFulfilled);
    }

    #[DataProvider('invalidStrings')]
    #[Test]
    public function simpleStrategyReturnFalseForStringsWitUnderscoreOrMinusOrSpace(string $string): void
    {
        $isFulfilled = $this->strategy->isFulfilled($string);

        $this->assertFalse($isFulfilled);
    }

    #[Test]
    public function simpleStrategyReturnGivenParameterWithoutModification(): void
    {
        $string = $this->strategy->getName('simpleCamelCase');

        $this->assertSame('simpleCamelCase', $string);
    }

    public static function validStrings(): Iterator
    {
        yield 'simple string' => ['string'];
        yield 'camelCase string' => ['validString'];
        yield 'string with number' => ['valid1'];
        yield 'camelCase with number' => ['validString123String'];
    }

    public static function invalidStrings(): Iterator
    {
        yield 'string with space' => ['string string'];
        yield 'string with underscore' => ['string_string'];
        yield 'string with minus' => ['string-string'];
    }
}
