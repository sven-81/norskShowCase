<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\identityAccessManagement\jwt;

use InvalidArgumentException;
use norsk\api\user\domain\model\AuthToken;

readonly class JsonWebToken implements AuthToken
{
    private function __construct(private string $token)
    {
    }


    public static function fromString(string $token): self
    {
        self::ensureTokenIsJwtValid($token);

        return new self($token);
    }


    private static function ensureTokenIsJwtValid(string $token): void
    {
        if (!preg_match("/^([a-zA-Z0-9_=]{4,})\.([a-zA-Z0-9_=]{4,})\.([a-zA-Z0-9_\-+\/=]{4,})$/", $token)) {
            throw new InvalidArgumentException('Token has no valid format');
        }
    }


    public static function fromBearerString(string $bearerToken): self
    {
        $jwtToken = str_replace('Bearer ', '', $bearerToken);

        return new self($jwtToken);
    }


    public function asString(): string
    {
        return $this->token;
    }
}
