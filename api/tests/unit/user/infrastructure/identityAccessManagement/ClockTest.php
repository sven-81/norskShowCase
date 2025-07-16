<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\identityAccessManagement;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Clock::class)]
class ClockTest extends TestCase
{
    public function testCanCreateNowByCall(): void
    {
        $clock = new Clock();
        self::assertEquals(
            (new DateTimeImmutable('now'))->format('Y-m-d H:i:s'),
            $clock->now()->format('Y-m-d H:i:s')
        );
    }
}
