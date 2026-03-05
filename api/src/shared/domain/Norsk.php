<?php

declare(strict_types=1);

namespace norsk\api\shared\domain;

use InvalidArgumentException;
use norsk\api\shared\application\SanitizedClientInput;

readonly class Norsk
{
    private function __construct(private string $norsk)
    {
    }


    public static function of(string $string): self
    {
        $trimmed = trim($string);
        self::ensureIsNotEmpty($trimmed);

        return new self(SanitizedClientInput::of($trimmed)->asString());
    }


    private static function ensureIsNotEmpty(string $string): void
    {
        if ($string === '') {
            throw new InvalidArgumentException('Norsk cannot be empty.', DomainExceptionCode::invalidInput->value);
        }
    }


    public function asString(): string
    {
        return $this->norsk;
    }
}
