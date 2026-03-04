<?php

declare(strict_types=1);

namespace norsk\api\user\domain\valueObjects;

use InvalidArgumentException;
use norsk\api\shared\application\SanitizedClientInput;

readonly class LastName
{
    private function __construct(private string $lastName)
    {
    }


    public static function by(string $lastName): self
    {
        $trimmed = trim($lastName);

        self::ensureIsNotEmpty($trimmed);

        return new self(SanitizedClientInput::of($trimmed)->asString());
    }


    private static function ensureIsNotEmpty(string $trimmed): void
    {
        if ($trimmed === '') {
            throw new InvalidArgumentException('Last name cannot be empty.');
        }
    }


    public function asString(): string
    {
        return $this->lastName;
    }
}
