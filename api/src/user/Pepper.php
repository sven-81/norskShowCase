<?php

declare(strict_types=1);

namespace norsk\api\user;

use RuntimeException;
use SensitiveParameter;

class Pepper
{
    private const int LENGTH = 32;


    private function __construct(#[SensitiveParameter] private readonly ?string $pepper)
    {
    }


    public static function by(string $string): self
    {
        self::ensureIsNotNull($string);
        self::ensureIsLongEnough($string);

        return new self($string);
    }


    private static function ensureIsNotNull(string $pepper): void
    {
        if ($pepper === '') {
            throw new RuntimeException('Pepper not set');
        }
    }


    private static function ensureIsLongEnough(string $pepper): void
    {
        if (strlen($pepper) < self::LENGTH) {
            throw new RuntimeException('Pepper is too short');
        }
    }


    public function asString(): string
    {
        return $this->pepper;
    }
}
