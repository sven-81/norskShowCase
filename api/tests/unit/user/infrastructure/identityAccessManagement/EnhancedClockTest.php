<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\identityAccessManagement;

use DateTimeImmutable;
use norsk\api\tests\stubs\MutableTestClock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EnhancedClock::class)]
class EnhancedClockTest extends TestCase
{
    private const int TWO_HOURS_IN_SECONDS = 7200;

    private EnhancedClock $initialClock;

    private string $initialDateTimeString;


    protected function setUp(): void
    {
        $this->initialDateTimeString = '2019-05-04 13:41:30';
        $this->initialClock = new EnhancedClock(new MutableTestClock($this->initialDateTimeString));
    }


    public function testCanCreateNowByCall(): void
    {
        $clock = new EnhancedClock($this->initialClock);
        $nowByClock = $clock->now();
        self::assertEquals($clock->getTimestamp(), $nowByClock->getTimestamp());
    }


    public function testCanGetTimestamp(): void
    {
        self::assertEquals(1556970090, $this->initialClock->getTimestamp());
    }


    public function testCanAddSecondsToTimestamp(): void
    {
        $newTime = $this->initialClock->addSeconds(seconds: self::TWO_HOURS_IN_SECONDS);

        self::assertEquals(1556977290, $newTime->getTimestamp(), 'addSeconds creates new object');
        self::assertEquals(
            $this->initialDateTimeString,
            (new DateTimeImmutable())->setTimestamp($this->initialClock->getTimestamp())->format('Y-m-d H:i:s'),
            'initial Clock is not modified'
        );
    }
}
