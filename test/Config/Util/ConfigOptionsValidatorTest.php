<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Installer\Config\Util;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Shlinkio\Shlink\Installer\Config\Util\ConfigOptionsValidator;
use Shlinkio\Shlink\Installer\Exception\InvalidConfigOptionException;
use Shlinkio\Shlink\Installer\Exception\MissingRequiredOptionException;

class ConfigOptionsValidatorTest extends TestCase
{
    #[Test]
    public function throwsAnExceptionIfInvalidUrlIsProvided(): void
    {
        $this->expectException(InvalidConfigOptionException::class);
        $this->expectExceptionMessage('Provided value "something" is not a valid URL');

        ConfigOptionsValidator::validateUrl('something');
    }

    #[Test, DataProvider('provideInvalidValues')]
    public function validateNumberGreaterThanThrowsExceptionWhenProvidedValueIsInvalid(array $args): void
    {
        $this->expectException(InvalidConfigOptionException::class);
        ConfigOptionsValidator::validateNumberGreaterThan(...$args);
    }

    public static function provideInvalidValues(): iterable
    {
        yield 'string' => [['foo', 1]];
        yield 'empty string' => [['', 1]];
        yield 'negative number' => [[-5, 1]];
        yield 'negative number as string' => [['-5', 1]];
        yield 'zero' => [[0, 1]];
        yield 'zero as string' => [['0', 1]];
        yield 'null' => [[null, 1]];
        yield 'positive with min' => [[5, 6]];
    }

    #[Test, DataProvider('providePositiveNumbers')]
    public function validatePositiveNumberCastsToIntWhenProvidedValueIsValid(mixed $value, int $expected): void
    {
        self::assertEquals($expected, ConfigOptionsValidator::validatePositiveNumber($value));
    }

    public static function providePositiveNumbers(): iterable
    {
        yield 'positive as string' => ['20', 20];
        yield 'positive as integer' => [5, 5];
        yield 'one as string' => ['1', 1];
        yield 'one as integer' => [1, 1];
    }

    #[Test, DataProvider('provideOptionalPositiveNumbers')]
    public function validateOptionalPositiveNumberCastsToIntWhenProvidedValueIsValid(
        mixed $value,
        int|null $expected,
    ): void {
        self::assertEquals($expected, ConfigOptionsValidator::validateOptionalPositiveNumber($value));
    }

    public static function provideOptionalPositiveNumbers(): iterable
    {
        yield 'null' => [null, null];
        yield from self::providePositiveNumbers();
    }

    #[Test, DataProvider('provideInvalidNumbersBetween')]
    public function validateNumberBetweenThrowsExceptionWhenProvidedValueIsInvalid(
        mixed $value,
        int $min,
        int $max,
    ): void {
        $this->expectException(InvalidConfigOptionException::class);
        ConfigOptionsValidator::validateNumberBetween($value, $min, $max);
    }

    public static function provideInvalidNumbersBetween(): iterable
    {
        yield 'string' => ['foo', 1, 2];
        yield 'lower as int' => [10, 20, 30];
        yield 'lower as string' => ['10', 20, 30];
        yield 'right before as int' => [19, 20, 30];
        yield 'right before as string' => ['19', 20, 30];
        yield 'right after as int' => [31, 20, 30];
        yield 'right after as string' => ['31', 20, 30];
        yield 'greater as int' => [50, 20, 30];
        yield 'greater as string' => ['300', 20, 30];
        yield 'impossible range' => [15, 30, 20];
    }

    #[Test, DataProvider('provideValidNumbersBetween')]
    public function validateNumberBetweenCastsToIntWhenProvidedValueIsValid(
        mixed $value,
        int $min,
        int $max,
        int $expected,
    ): void {
        self::assertEquals($expected, ConfigOptionsValidator::validateNumberBetween($value, $min, $max));
    }

    public static function provideValidNumbersBetween(): iterable
    {
        yield 'first as string' => ['20', 20, 30, 20];
        yield 'first as int' => [20, 20, 30, 20];
        yield 'between as string' => ['30', 20, 40, 30];
        yield 'between as int' => [25, 20, 40, 25];
        yield 'last as string' => ['55', 20, 55, 55];
        yield 'last as int' => [55, 20, 55, 55];
    }

    #[Test, DataProvider('provideInvalidColors')]
    public function validateHexColorThrowsForInvalidValues(string $color, string $expectedMessage): void
    {
        $this->expectException(InvalidConfigOptionException::class);
        $this->expectExceptionMessage($expectedMessage);

        ConfigOptionsValidator::validateHexColor($color);
    }

    public static function provideInvalidColors(): iterable
    {
        yield ['11', 'Provided value must have 3 or 6 characters, and be optionally preceded by the # character'];
        yield ['1111', 'Provided value must have 3 or 6 characters, and be optionally preceded by the # character'];
        yield ['11111', 'Provided value must have 3 or 6 characters, and be optionally preceded by the # character'];
        yield ['1111111', 'Provided value must have 3 or 6 characters, and be optionally preceded by the # character'];
        yield ['#11', 'Provided value must have 3 or 6 characters, and be optionally preceded by the # character'];
        yield ['#1111', 'Provided value must have 3 or 6 characters, and be optionally preceded by the # character'];
        yield ['#11111', 'Provided value must have 3 or 6 characters, and be optionally preceded by the # character'];
        yield ['#1111111', 'Provided value must have 3 or 6 characters, and be optionally preceded by the # character'];
        yield ['foo', 'Provided value must be the hexadecimal number representation of a color'];
        yield ['foobar', 'Provided value must be the hexadecimal number representation of a color'];
        yield ['#foo', 'Provided value must be the hexadecimal number representation of a color'];
        yield ['#foobar', 'Provided value must be the hexadecimal number representation of a color'];
    }

    #[Test, DataProvider('provideValidColors')]
    public function validateHexColorReturnsOriginalValueWhenValid(string $color): void
    {
        self::assertSame($color, ConfigOptionsValidator::validateHexColor($color));
    }

    public static function provideValidColors(): iterable
    {
        yield ['#111'];
        yield ['111'];
        yield ['#aaaaaa'];
        yield ['aaa'];
    }

    #[Test]
    #[TestWith(['2048'])]
    #[TestWith(['1G'])]
    #[TestWith(['1g'])]
    #[TestWith(['1024K'])]
    #[TestWith(['1024k'])]
    #[TestWith(['256M'])]
    #[TestWith(['256m'])]
    public function validateMemoryValueReturnsValidValues(string $value): void
    {
        self::assertEquals($value, ConfigOptionsValidator::validateMemoryValue($value));
    }

    #[Test]
    #[TestWith(['aaaa'])]
    #[TestWith(['foo'])]
    #[TestWith(['1gb'])]
    #[TestWith(['1024KB'])]
    #[TestWith(['100.86'])]
    public function validateMemoryValueThrowsWhenAnInvalidValueIsProvided(string $invalidValue): void
    {
        $this->expectException(InvalidConfigOptionException::class);
        ConfigOptionsValidator::validateMemoryValue($invalidValue);
    }

    #[Test]
    public function validateRequiredThrowsWhenValueIsEmpty(): void
    {
        $this->expectException(MissingRequiredOptionException::class);
        $this->expectExceptionMessage('The "name" is required and can\'t be empty');

        ConfigOptionsValidator::validateRequired('', 'name');
    }

    #[Test]
    public function validateRequiredReturnsValueWhenNotEmpty(): void
    {
        self::assertEquals('foo', ConfigOptionsValidator::validateRequired('foo', 'name'));
    }
}
