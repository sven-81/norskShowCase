<?php

declare(strict_types=1);

namespace norsk\api\app\identityAccessManagement;

use InvalidArgumentException;
use norsk\api\shared\SanitizedClientInput;

readonly class JwtSubject
{
    private function __construct(private string $jwtSubject)
    {
    }


    public static function by(string $jwtSubject): self
    {
        $trimmed = trim($jwtSubject);

        self::ensureIsNotEmpty($trimmed);

        return new self(SanitizedClientInput::of($trimmed)->asString());
    }


    private static function ensureIsNotEmpty(string $jwtSubject): void
    {
        if ($jwtSubject === '') {
            throw new InvalidArgumentException('Subject in JWT cannot be empty.');
        }
    }


    public function asString(): string
    {
        return $this->jwtSubject;
    }
}
