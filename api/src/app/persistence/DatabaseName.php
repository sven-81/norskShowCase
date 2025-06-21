<?php

declare(strict_types=1);

namespace norsk\api\app\persistence;

use InvalidArgumentException;
use norsk\api\app\response\ResponseCode;

class DatabaseName
{
    private function __construct(private readonly string $name)
    {
    }


    public static function fromString(string $name): self
    {
        self::ensureIsNotEmpty(trim($name));

        return new self($name);
    }


    private static function ensureIsNotEmpty(string $name): void
    {
        if ($name === '') {
            throw new InvalidArgumentException('Name is not set', ResponseCode::unprocessable->value);
        }
    }


    public function asString(): string
    {
        return $this->name;
    }
}
