<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\persistence;

use norsk\api\infrastructure\persistence\SqlResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SqlResult::class)]
class SqlResultTest extends TestCase
{
    public static function arrayAmount(): array
    {
        $array1 = [];
        $array2 = [
            1 => [],
        ];
        $array3 = [
            0 => [],
            1 => [],
            2 => [],
        ];

        return [
            [$array1, 0],
            [$array2, 1],
            [$array3, 3],
        ];
    }


    public static function filledArrays(): array
    {
        $array1 = [
            1 => ['x'],
        ];
        $array2 = [
            0 => ['x'],
            1 => ['x'],
            2 => ['x'],
        ];
        $array3 = [
            1 => 'else',
        ];

        return [
            [$array1],
            [$array2],
            [$array3],
        ];
    }


    public function testCanBeInitialized(): void
    {
        self::assertInstanceOf(SqlResult::class, SqlResult::resultFromArray([]));
    }


    public function testCanBeUsedAsArray(): void
    {
        $array = [
            1 => [
                'foo' => 'bar',
            ],
        ];
        $this->assertSame($array, SqlResult::resultFromArray($array)->asArray());
    }


    public function testCanGetIterated(): void
    {
        $array = [
            1 => [
                'foo' => 'bar',
            ],
        ];
        $this->assertSame(1, SqlResult::resultFromArray($array)->getIterator()->key());
    }


    #[DataProvider('arrayAmount')]
    public function testCountReturnsCorrectIntOfElements(
        array $array,
        int $expectedAmount
    ): void {
        $this->assertSame($expectedAmount, SqlResult::resultFromArray($array)->count());
    }


    public function testReturnsFalseIfResultIsEmpty(): void
    {
        $array = [];
        $this->assertFalse(SqlResult::resultFromArray($array)->hasEntries());
    }


    #[DataProvider('filledArrays')]
    public function testReturnsTrueIfResultHasAtLeastOneEntry(array $array): void
    {
        $this->assertTrue(SqlResult::resultFromArray($array)->hasEntries());
    }


    public function testReturnsTrueIfResultHasAnAmountBySqlCountEntry(): void
    {
        $array[0]['match'] = '1';

        $this->assertTrue(SqlResult::resultFromArray($array)->hasEntries());
    }


    public function testReturnsFalseIfResultHasNoAmountBySqlCountEntry(): void
    {
        $array[0]['match'] = '0';

        $this->assertFalse(SqlResult::resultFromArray($array)->hasEntries());
    }
}
