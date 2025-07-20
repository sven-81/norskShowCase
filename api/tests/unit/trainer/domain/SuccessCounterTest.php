<?php

declare(strict_types=1);

namespace norsk\api\trainer\domain;

use OutOfRangeException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertFalse;

#[CoversClass(SuccessCounter::class)]
class SuccessCounterTest extends TestCase
{
    public function testCanBeUsedAsInt(): void
    {
        $this->assertSame(123, SuccessCounter::by(123)->asInt());
    }


    public function testCanBeUsedAsIntWithZero(): void
    {
        $this->assertSame(0, SuccessCounter::by(0)->asInt());
    }


    public function testCanBeUsedAsIntZeroFromNull(): void
    {
        $this->assertSame(0, SuccessCounter::by(null)->asInt());
    }


    public function testThrowsExceptionIfNegativeNumberIsGiven(): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage('Given SuccessCounter is not a positive number:');

        SuccessCounter::by(-2)->asInt();
    }


    public function testReturnsTrueIfIsInitial(): void
    {
        self::assertTrue(SuccessCounter::by(null)->isInitial());
    }


    public function testReturnsFalseIfIsNotInitial(): void
    {
        assertFalse(SuccessCounter::by(123)->isInitial());
    }
}
