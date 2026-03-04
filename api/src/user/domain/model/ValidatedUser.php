<?php

declare(strict_types=1);

namespace norsk\api\user\domain\model;

use norsk\api\user\domain\exceptions\NoActiveUserException;
use norsk\api\user\domain\valueObjects\FirstName;
use norsk\api\user\domain\valueObjects\InputPassword;
use norsk\api\user\domain\valueObjects\LastName;
use norsk\api\user\domain\valueObjects\PasswordHash;
use norsk\api\user\domain\valueObjects\PasswordVector;
use norsk\api\user\domain\valueObjects\Pepper;
use norsk\api\user\domain\valueObjects\UserName;
use norsk\api\shared\domain\DomainExceptionCode;

readonly class ValidatedUser
{
    private function __construct(
        private UserName $userName,
        private FirstName $firstName,
        private LastName $lastName,
        private Role $role,
    ) {
    }


    public static function fromUserData(UserData $userData, InputPassword $inputPassword, Pepper $pepper): self
    {
        $passwordVector = PasswordVector::by($userData->salt, $pepper);

        PasswordHash::byValidatedInputPassword(
            $inputPassword,
            $passwordVector,
            $userData->passwordHash
        );

        self::ensureUserIsActive($userData->isActive);

        return new self($userData->userName, $userData->firstName, $userData->lastName, $userData->role);
    }


    private static function ensureUserIsActive(bool $isActive): void
    {
        if (!$isActive) {
            throw new NoActiveUserException('Forbidden: user is not active', DomainExceptionCode::forbidden->value);
        }
    }


    public function getUserName(): UserName
    {
        return $this->userName;
    }


    public function getFirstName(): FirstName
    {
        return $this->firstName;
    }


    public function getLastName(): LastName
    {
        return $this->lastName;
    }


    public function getRole(): Role
    {
        return $this->role;
    }
}
