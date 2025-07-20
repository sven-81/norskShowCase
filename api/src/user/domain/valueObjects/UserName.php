<?php

declare(strict_types=1);

namespace norsk\api\user\domain\valueObjects;

use InvalidArgumentException;
use norsk\api\shared\application\SanitizedClientInput;
use norsk\api\shared\infrastructure\http\response\ResponseCode;

readonly class UserName
{
    private const int MIN_LENGTH = 4;
    private const int MAX_LENGTH = 30;


    private function __construct(private string $userName)
    {
    }


    public static function by(string $userName): self
    {
        $trimmed = trim($userName);
        self::ensureHasValidLength($trimmed);
        $sanitized = self::ensureContainsNoInvalidChars($trimmed);

        return new self($sanitized);
    }


    private static function ensureHasValidLength(string $trimmed): void
    {
        if ((strlen($trimmed) < self::MIN_LENGTH) || (strlen($trimmed) > self::MAX_LENGTH)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The username must be between %d and %d characters long.',
                    self::MIN_LENGTH,
                    self::MAX_LENGTH
                ),
                ResponseCode::unprocessable->value
            );
        }
    }


    private static function ensureContainsNoInvalidChars(string $trimmed): string
    {
        $sanitized = SanitizedClientInput::of($trimmed)->asString();
        if ($sanitized !== $trimmed) {
            throw new InvalidArgumentException(
                'User name contains invalid characters: \' or &.',
                ResponseCode::unprocessable->value
            );
        }

        return $sanitized;
    }


    public function asString(): string
    {
        return $this->userName;
    }
}
