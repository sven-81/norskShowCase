<?php

declare(strict_types=1);

namespace norsk\api\user\domain\model;

use norsk\api\user\domain\valueObjects\FirstName;
use norsk\api\user\domain\valueObjects\LastName;
use norsk\api\user\domain\valueObjects\PasswordHash;
use norsk\api\user\domain\valueObjects\Salt;
use norsk\api\user\domain\valueObjects\UserName;


readonly class UserData
{
    private function __construct(
        public UserName $userName,
        public FirstName $firstName,
        public LastName $lastName,
        public PasswordHash $passwordHash,
        public Salt $salt,
        public Role $role,
        public bool $isActive,
    ) {
    }


    public static function of(
        UserName $userName,
        FirstName $firstName,
        LastName $lastName,
        PasswordHash $passwordHash,
        Salt $salt,
        Role $role,
        bool $isActive,
    ): self {
        return new self($userName, $firstName, $lastName, $passwordHash, $salt, $role, $isActive);
    }
}

