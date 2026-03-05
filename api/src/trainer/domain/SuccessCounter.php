<?php

declare(strict_types=1);

namespace norsk\api\trainer\domain;

use norsk\api\shared\domain\DomainExceptionCode;
use OutOfRangeException;

class SuccessCounter
{
    private function __construct(private readonly int $number)
    {
    }


    public static function by(?int $number): self
    {
        $number = self::convertNullToZero($number);
        self::ensureOnlyPositives($number);

        return new self($number);
    }


    private static function convertNullToZero(?int $number): int
    {
        if ($number === null) {
            return 0;
        }

        return $number;
    }


    private static function ensureOnlyPositives(int $number): void
    {
        if ($number < 0) {
            throw new OutOfRangeException(
                'Given SuccessCounter is not a positive number: ' . $number,
                DomainExceptionCode::invalidInput->value
            );
        }
    }


    public function isInitial(): bool
    {
        return $this->asInt() === 0;
    }


    public function asInt(): int
    {
        return $this->number;
    }
}
