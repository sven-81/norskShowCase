<?php

declare(strict_types=1);

namespace norsk\api\user;

use norsk\api\app\request\Parameter;
use norsk\api\app\request\Payload;
use norsk\api\user\exceptions\ParameterMissingException;

readonly class RegisteredUser
{
    private const string USERNAME = 'username';
    private const string FIRST_NAME = 'firstName';
    private const string LAST_NAME = 'lastName';
    private const string PASSWORD = 'password';


    private function __construct(
        private UserName $userName,
        private FirstName $firstName,
        private LastName $lastName,
        private PasswordHash $passwordHash,
        private Salt $salt,
    ) {
    }


    public static function createByPayload(Payload $payload, PasswordVector $passwordVector): self
    {
        $payloadArray = $payload->asArray();
        self::ensureParameterExist($payloadArray);

        $userName = UserName::by($payloadArray[self::USERNAME]);
        $firstName = FirstName::by($payloadArray[self::FIRST_NAME]);
        $lastName = LastName::by($payloadArray[self::LAST_NAME]);
        $passwordHash = PasswordHash::hashBy(
            InputPassword::by($payloadArray[self::PASSWORD]),
            $passwordVector
        );

        $salt = $passwordVector->getSalt();

        return new self($userName, $firstName, $lastName, $passwordHash, $salt);
    }


    private static function ensureParameterExist(array $payloadArray): void
    {
        $neededKeys = [
            self::USERNAME,
            self::FIRST_NAME,
            self::LAST_NAME,
            self::PASSWORD,
        ];

        foreach ($neededKeys as $neededKey) {
            if (!array_key_exists($neededKey, $payloadArray)) {
                throw new ParameterMissingException(Parameter::by($neededKey));
            }
        }
    }


    public function getSalt(): Salt
    {
        return $this->salt;
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


    public function getPasswordHash(): PasswordHash
    {
        return $this->passwordHash;
    }
}
