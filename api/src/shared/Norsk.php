<?php

declare(strict_types=1);

namespace norsk\api\shared;

use InvalidArgumentException;
use norsk\api\app\response\ResponseCode;

class Norsk
{
    private function __construct(private readonly string $norsk)
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
            throw new InvalidArgumentException('Norsk cannot be empty.', ResponseCode::unprocessable->value);
        }
    }


    public function asString(): string
    {
        return $this->norsk;
    }
}
