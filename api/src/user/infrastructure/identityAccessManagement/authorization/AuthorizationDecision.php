<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\identityAccessManagement\authorization;

use norsk\api\user\domain\model\Role;
use norsk\api\user\domain\valueObjects\UserName;

readonly class AuthorizationDecision
{

    private function __construct(
        private bool $isAuthorized,
        private ?UserName $userName,
        private ?Role $role
    ) {
    }


    public static function by(
        bool $isAuthorized = false,
        ?UserName $userName = null,
        ?Role $role = null
    ): self {
        return new self($isAuthorized, $userName, $role);
    }


    public function isAuthorized(): bool
    {
        return $this->isAuthorized;
    }


    public function getUserName(): ?UserName
    {
        return $this->userName;
    }


    public function getRole(): ?Role
    {
        return $this->role;
    }


    public function failed(): bool
    {
        return !$this->isAuthorized;
    }
}