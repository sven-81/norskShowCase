<?php

declare(strict_types=1);

namespace norsk\api\shared;

use InvalidArgumentException;
use norsk\api\app\response\ResponseCode;

class German
{
    private const int SHORTEST_WORD = 2;


    private function __construct(private readonly string $german)
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
        $strlen = strlen($string);
        if ($strlen < self::SHORTEST_WORD) {
            throw new InvalidArgumentException(
                'German has at least two chars.',
                ResponseCode::unprocessable->value
            );
        }
    }


    public function asString(): string
    {
        return $this->german;
    }
}
