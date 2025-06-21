<?php

declare(strict_types=1);

namespace norsk\api\app\persistence;

use InvalidArgumentException;
use norsk\api\app\response\ResponseCode;

readonly class Port
{
    private function __construct(private int $port)
    {
    }


    public static function fromInt(int $port): self
    {
        self::ensureIsNotZero($port);

        return new self($port);
    }


    private static function ensureIsNotZero(int $port): void
    {
        if ($port === 0) {
            throw new InvalidArgumentException('Port is not set', ResponseCode::unprocessable->value);
        }
    }


    public function asInt(): int
    {
        return $this->port;
    }
}
