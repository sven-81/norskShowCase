<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\identityAccessManagement;

use DateTimeImmutable;
use DateTimeZone;
use Psr\Clock\ClockInterface;

class Clock implements ClockInterface
{
    private const string EUROPE_BERLIN = 'Europe/Berlin';


    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone(self::EUROPE_BERLIN));
    }
}
