<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\persistence;

use InvalidArgumentException;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use SensitiveParameter;

class Password
{
    private function __construct(private readonly string $password)
    {
    }


    public static function fromString(
        #[SensitiveParameter]
        string $password
    ): self {
        self::ensureIsNotEmpty(trim($password));

        return new self($password);
    }


    private static function ensureIsNotEmpty(string $password): void
    {
        if ($password === '') {
            throw new InvalidArgumentException('Password is not set', ResponseCode::unprocessable->value);
        }
    }


    public function asString(): string
    {
        return $this->password;
    }
}
