<?php

declare(strict_types=1);

namespace norsk\api\app\identityAccessManagement;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertNotEquals;

#[CoversClass(Clock::class)]
class ClockTest extends TestCase
{
    private const int TWO_HOURS_IN_SECONDS = 7200;


    public function testCanGetTimestamp(): void
    {
        $clock = new Clock(new DateTimeImmutable('2019-05-04 13:41:30'));
        self::assertEquals(1556970090, $clock->getTimestamp());
    }


    public function testCanAddSecondsToTimestamp(): void
    {
        $initialTime = '2019-05-04 13:41:30';
        $clock = new Clock(new DateTimeImmutable($initialTime));
        $newTime = $clock->addSeconds(seconds: self::TWO_HOURS_IN_SECONDS);

        self::assertEquals(1556977290, $newTime->getTimestamp(), 'addSeconds creates new object');
        self::assertEquals(
            $initialTime,
            (new DateTimeImmutable())->setTimestamp($clock->getTimestamp())->format('Y-m-d H:i:s'),
            'initial Clock is not modified'
        );
    }


    public function testCanCreateNowByCall(): void
    {
        $clock = new Clock(new DateTimeImmutable('2019-05-04 13:41:30'));
        $nowByClock = $clock->now();
        assertNotEquals($clock->getTimestamp(), $nowByClock->getTimestamp());
    }
}
