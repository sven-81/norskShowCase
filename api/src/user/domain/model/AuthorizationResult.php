<?php

declare(strict_types=1);

namespace norsk\api\user\domain\model;

use norsk\api\user\domain\valueObjects\UserName;

readonly class AuthorizationResult
{
    private function __construct(
        private bool $isAuthorized,
        private ?UserName $userName,
        private ?Role $role,
    ) {
    }


    public static function granted(UserName $userName, Role $role): self
    {
        return new self(true, $userName, $role);
    }


    public static function denied(): self
    {
        return new self(false, null, null);
    }


    public function isAuthorized(): bool
    {
        return $this->isAuthorized;
    }


    public function wasDenied(): bool
    {
        return !$this->isAuthorized;
    }


    public function getUserName(): ?UserName
    {
        return $this->userName;
    }


    public function getRole(): ?Role
    {
        return $this->role;
    }
}
