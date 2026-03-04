<?php

declare(strict_types=1);

namespace norsk\api\user\domain\valueObjects;

use InvalidArgumentException;
use norsk\api\shared\application\SanitizedClientInput;
use RuntimeException;
use SensitiveParameter;

class Salt
{
    private const int LENGTH = 32;


    private function __construct(#[SensitiveParameter] private ?string $salt)
    {
    }


    public static function init(): self
    {
        return new self(null);
    }


    public static function by(string $salt): self
    {
        $trimmed = trim($salt);
        self::ensureIsNotEmpty($trimmed);
        self::ensureIs32CharsLong($trimmed);

        return new self(SanitizedClientInput::of($salt)->asString());
    }


    private static function ensureIsNotEmpty(string $salt): void
    {
        if ($salt === '') {
            throw new InvalidArgumentException('Salt is empty');
        }
    }


    private static function ensureIs32CharsLong(string $salt): void
    {
        if (strlen($salt) < self::LENGTH) {
            throw new InvalidArgumentException('Salt does not have at least 32 characters');
        }
    }


    public function asString(): string
    {
        $this->ensureSaltIsNotNull();

        return $this->salt;
    }


    private function ensureSaltIsNotNull(): void
    {
        if (null === $this->salt) {
            throw new RuntimeException('Salt not set');
        }
    }


    public function generate(): void
    {
        $this->salt = bin2hex(random_bytes(self::LENGTH));
    }
}
