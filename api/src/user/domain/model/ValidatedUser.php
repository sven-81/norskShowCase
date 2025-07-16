<?php

declare(strict_types=1);

namespace norsk\api\user\domain\model;

use InvalidArgumentException;
use norsk\api\infrastructure\persistence\SqlResult;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\user\domain\exceptions\NoActiveUserException;
use norsk\api\user\domain\valueObjects\FirstName;
use norsk\api\user\domain\valueObjects\InputPassword;
use norsk\api\user\domain\valueObjects\LastName;
use norsk\api\user\domain\valueObjects\PasswordHash;
use norsk\api\user\domain\valueObjects\PasswordVector;
use norsk\api\user\domain\valueObjects\Pepper;
use norsk\api\user\domain\valueObjects\Salt;
use norsk\api\user\domain\valueObjects\UserName;

readonly class ValidatedUser
{
    private const string USERNAME = 'username';
    private const string FIRST_NAME = 'firstname';
    private const string LAST_NAME = 'lastname';
    private const string PASSWORD_HASH = 'password_hash';
    private const string SALT = 'salt';
    private const string ACTIVE = 'active';
    private const string ROLE = 'role';


    private function __construct(
        private UserName $userName,
        private FirstName $firstName,
        private LastName $lastName,
        private Role $role,
    ) {
    }


    public static function createBySqlResultAndPasswordHash(
        SqlResult $sqlResult,
        InputPassword $inputPassword,
        Pepper $pepper
    ): self {
        $resultArray = $sqlResult->asArray()[0];
        self::ensureParameterExist($resultArray);
        self::ensureUserIsActive($resultArray[self::ACTIVE]);

        $userName = UserName::by($resultArray[self::USERNAME]);
        $firstName = FirstName::by($resultArray[self::FIRST_NAME]);
        $lastName = LastName::by($resultArray[self::LAST_NAME]);
        $role = Role::from($resultArray[self::ROLE]);

        $storedPasswordHash = PasswordHash::by($resultArray[self::PASSWORD_HASH]);
        $storedSalt = Salt::by($resultArray[self::SALT]);
        $passwordVector = PasswordVector::by($storedSalt, $pepper);

        PasswordHash::byValidatedInputPassword(
            $inputPassword,
            $passwordVector,
            $storedPasswordHash
        );

        return new self($userName, $firstName, $lastName, $role);
    }


    private static function ensureParameterExist(array $payloadArray): void
    {
        $neededKeys = [
            self::USERNAME,
            self::FIRST_NAME,
            self::LAST_NAME,
            self::PASSWORD_HASH,
            self::ROLE,
            self::ACTIVE,
        ];

        foreach ($neededKeys as $neededKey) {
            if (!array_key_exists($neededKey, $payloadArray)) {
                throw new InvalidArgumentException('Missing field: ' . $neededKey);
            }
        }
    }


    private static function ensureUserIsActive(int $active): void
    {
        if (self::isNotActive($active)) {
            throw new NoActiveUserException('Forbidden: user is not active', ResponseCode::forbidden->value);
        }
    }


    private static function isNotActive(int $active): bool
    {
        return $active !== 1;
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
