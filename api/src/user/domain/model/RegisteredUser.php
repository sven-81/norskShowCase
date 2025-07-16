<?php

declare(strict_types=1);

namespace norsk\api\user\domain\model;

use norsk\api\user\domain\valueObjects\FirstName;
use norsk\api\user\domain\valueObjects\InputPassword;
use norsk\api\user\domain\valueObjects\LastName;
use norsk\api\user\domain\valueObjects\PasswordHash;
use norsk\api\user\domain\valueObjects\PasswordVector;
use norsk\api\user\domain\valueObjects\Salt;
use norsk\api\user\domain\valueObjects\UserName;

readonly class RegisteredUser
{
    private function __construct(
        private UserName $userName,
        private FirstName $firstName,
        private LastName $lastName,
        private PasswordHash $passwordHash,
        private Salt $salt,
    ) {
    }


    public static function create(
        UserName $userName,
        FirstName $firstName,
        LastName $lastName,
        InputPassword $inputPassword,
        PasswordVector $passwordVector
    ): self {
        $passwordHash = PasswordHash::hashBy($inputPassword, $passwordVector);
        $salt = $passwordVector->getSalt();

        return new self($userName, $firstName, $lastName, $passwordHash, $salt);
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
