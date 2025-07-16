<?php

declare(strict_types=1);

namespace norsk\api\user\domain\valueObjects;

use norsk\api\shared\application\SanitizedClientInput;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\user\domain\exceptions\CredentialsAreInvalidException;
use norsk\api\user\domain\exceptions\PasswordIsInvalidException;
use SensitiveParameter;

readonly class PasswordHash
{
    private const int MIN_PASSWORD_LENGTH = 12;
    private const int SECONDS_0_1 = 100000;
    private const int SECONDS_0_5 = 500000;


    private function __construct(
        #[SensitiveParameter]
        private string $passwordHash
    ) {
    }


    public static function hashBy(
        #[SensitiveParameter]
        InputPassword $inputPassword,
        #[SensitiveParameter]
        PasswordVector $passwordVector
    ): self {
        $passwordString = $inputPassword->asString();
        self::ensureIsLongEnough($passwordString);
        $hash = self::generateHash($passwordString, $passwordVector);

        return new self($hash);
    }


    private static function ensureIsLongEnough(string $password): void
    {
        if (strlen($password) < self::MIN_PASSWORD_LENGTH) {
            throw new PasswordIsInvalidException(
                'Password is too short',
                ResponseCode::unprocessable->value
            );
        }
    }


    private static function generateHash(string $password, PasswordVector $passwordVector): string
    {
        $salt = $passwordVector->getSalt();
        $salt->generate();
        $pepper = $passwordVector->getPepper();

        $passwordWithSaltAndPepper = $password . $salt->asString() . $pepper->asString();

        $hashedPassword = password_hash($passwordWithSaltAndPepper, PASSWORD_BCRYPT);
        self::addRandomMillisecondsForAttacker();

        return $hashedPassword;
    }


    private static function addRandomMillisecondsForAttacker(): void
    {
        usleep((random_int(self::SECONDS_0_1, self::SECONDS_0_5)));
    }


    public static function by(string $passwordHash): self
    {
        $trimmed = trim($passwordHash);
        self::ensureIsNotEmpty($trimmed);

        return new self(SanitizedClientInput::of($trimmed)->asString());
    }


    private static function ensureIsNotEmpty(string $trimmed): void
    {
        if ($trimmed === '') {
            throw new PasswordIsInvalidException(
                'Password name cannot be empty',
                ResponseCode::unprocessable->value
            );
        }
    }


    public static function byValidatedInputPassword(
        InputPassword $inputPassword,
        PasswordVector $passwordVector,
        PasswordHash $storedHash
    ): self {
        $storedSalt = $passwordVector->getSalt();
        $pepper = $passwordVector->getPepper();
        $inputHash = $inputPassword->asString() . $storedSalt->asString() . $pepper->asString();

        if (self::inputPasswordDoesNotMatchSavedHash($inputHash, $storedHash)) {
            throw new CredentialsAreInvalidException();
        }

        return new self($storedHash->passwordHash);
    }


    private static function inputPasswordDoesNotMatchSavedHash(string $inputHash, PasswordHash $storedHash): bool
    {
        return !password_verify($inputHash, $storedHash->asHashString());
    }


    public function asHashString(): string
    {
        return $this->passwordHash;
    }
}
