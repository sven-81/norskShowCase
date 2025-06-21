<?php

declare(strict_types=1);

namespace norsk\api\user;

use InvalidArgumentException;
use norsk\api\shared\SanitizedClientInput;

readonly class FirstName
{
    private function __construct(private string $firstName)
    {
    }


    public static function by(string $firstName): self
    {
        $trimmed = trim($firstName);

        self::ensureIsNotEmpty($trimmed);

        return new self(SanitizedClientInput::of($trimmed)->asString());
    }


    private static function ensureIsNotEmpty(string $trimmed): void
    {
        if ($trimmed === '') {
            throw new InvalidArgumentException('First name cannot be empty.');
        }
    }


    public function asString(): string
    {
        return $this->firstName;
    }
}
