<?php

declare(strict_types=1);

namespace norsk\api\app\identityAccessManagement;

use DateTimeImmutable;
use DateTimeZone;
use Psr\Clock\ClockInterface;

class Clock implements ClockInterface
{
    private const string EUROPE_BERLIN = 'Europe/Berlin';


    public function __construct(private DateTimeImmutable $clock)
    {
        $this->clock = $clock->setTimezone(new DateTimeZone(self::EUROPE_BERLIN));
    }


    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone(self::EUROPE_BERLIN));
    }


    public function addSeconds(int $seconds): self
    {
        $newTime = $this->clock->modify('+' . $seconds . ' seconds');
        $newTime = $newTime->setTimezone(new DateTimeZone(self::EUROPE_BERLIN));

        return new self($newTime);
    }


    public function getTimestamp(): int
    {
        return $this->clock->getTimestamp();
    }
}
