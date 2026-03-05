<?php

declare(strict_types=1);

namespace norsk\api\tests\stubs;

use DateTimeImmutable;
use DateTimeZone;
use Psr\Clock\ClockInterface;

final class MutableTestClock implements ClockInterface
{
    private DateTimeImmutable $now;


    public function __construct(string $initialTime = 'now', string $timezone = 'Europe/Berlin')
    {
        $this->now = new DateTimeImmutable($initialTime, new DateTimeZone($timezone));
    }


    public function now(): DateTimeImmutable
    {
        return $this->now;
    }


    public function setTo(string $newTime, string $timezone = 'Europe/Berlin'): void
    {
        $this->now = new DateTimeImmutable($newTime, new DateTimeZone($timezone));
    }
}
