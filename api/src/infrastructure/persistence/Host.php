<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\persistence;

use InvalidArgumentException;
use norsk\api\shared\infrastructure\http\response\ResponseCode;

class Host
{
    private function __construct(private readonly string $host)
    {
    }


    public static function fromString(string $host): self
    {
        self::ensureIsNotEmpty(trim($host));

        return new self($host);
    }


    private static function ensureIsNotEmpty(string $host): void
    {
        if ($host === '') {
            throw new InvalidArgumentException('Host is not set', ResponseCode::unprocessable->value);
        }
    }


    public function asString(): string
    {
        return $this->host;
    }
}
