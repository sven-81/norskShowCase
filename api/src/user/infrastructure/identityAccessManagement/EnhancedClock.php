<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\identityAccessManagement;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

final class EnhancedClock implements ClockInterface
{
    public function __construct(private readonly ClockInterface $clock)
    {
    }


    public function now(): DateTimeImmutable
    {
        return $this->clock->now();
    }


    public function addSeconds(int $seconds): DateTimeImmutable
    {
        return $this->clock->now()->modify('+' . $seconds . ' seconds');
    }


    public function getTimestamp(): int
    {
        return $this->clock->now()->getTimestamp();
    }
}
