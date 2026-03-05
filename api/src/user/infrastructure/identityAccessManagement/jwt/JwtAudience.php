<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\identityAccessManagement\jwt;

use InvalidArgumentException;
use norsk\api\shared\application\SanitizedClientInput;

readonly class JwtAudience
{
    private function __construct(private string $jwtAudience)
    {
    }


    public static function by(string $jwtAudience): self
    {
        $trimmed = trim($jwtAudience);

        self::ensureIsNotEmpty($trimmed);

        return new self(SanitizedClientInput::of($trimmed)->asString());
    }


    private static function ensureIsNotEmpty(string $jwtAudience): void
    {
        if ($jwtAudience === '') {
            throw new InvalidArgumentException('Audience in JWT cannot be empty.');
        }
    }


    public function asString(): string
    {
        return $this->jwtAudience;
    }
}
