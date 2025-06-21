<?php

declare(strict_types=1);

namespace norsk\api\app\identityAccessManagement;

use InvalidArgumentException;

class AuthenticationAlgorithm
{
    private function __construct(private readonly string $algorithm)
    {
    }


    public static function by(string $algorithm): self
    {
        self::ensureAlgorithmIsJwtValid($algorithm);

        return new self($algorithm);
    }


    private static function ensureAlgorithmIsJwtValid(string $algorithm): void
    {
        $algorithmList = self::getAlgorithmList();

        if (!in_array($algorithm, $algorithmList)) {
            throw new InvalidArgumentException('Algorithm has no valid format');
        }
    }


    /**
     * @return string[]
     */
    private static function getAlgorithmList(): array
    {
        return [
            'HS256',
            'HS384',
            'HS512',
            'PS256',
            'PS384',
            'PS512',
            'RS256',
            'RS384',
            'RS512',
            'ES256',
            'ES256K',
            'ES384',
            'ES512',
            'EdDSA',
        ];
    }


    public function asString(): string
    {
        return $this->algorithm;
    }
}
