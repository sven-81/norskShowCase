<?php

declare(strict_types=1);

namespace norsk\api\user\domain\valueObjects;

use InvalidArgumentException;
use norsk\api\shared\application\SanitizedClientInput;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use SensitiveParameter;

readonly class InputPassword
{
    private const int MIN_LENGTH = 12;


    private function __construct(private string $password)
    {
    }


    public static function by(
        #[SensitiveParameter]
        string $password
    ): self {
        $trimmed = trim($password);

        self::ensureIsLongEnough($trimmed);
        $sanitized = self::ensureContainsNoInvalidChars($trimmed);

        return new self($sanitized);
    }


    private static function ensureIsLongEnough(string $trimmed): void
    {
        if ((strlen($trimmed) < self::MIN_LENGTH)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The password must be at least %d characters long.',
                    self::MIN_LENGTH
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
                'Password contains invalid characters: \' or &.',
                ResponseCode::unprocessable->value
            );
        }

        return $sanitized;
    }


    public function asString(): string
    {
        return $this->password;
    }
}
