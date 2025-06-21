<?php

declare(strict_types=1);

namespace norsk\api\app\persistence;

use InvalidArgumentException;
use norsk\api\app\response\ResponseCode;

class User
{
    private function __construct(private readonly string $user)
    {
    }


    public static function fromString(string $user): self
    {
        self::ensureIsNotEmpty(trim($user));

        return new self($user);
    }


    private static function ensureIsNotEmpty(string $user): void
    {
        if ($user === '') {
            throw new InvalidArgumentException('User is not set', ResponseCode::unprocessable->value);
        }
    }


    public function asString(): string
    {
        return $this->user;
    }
}
