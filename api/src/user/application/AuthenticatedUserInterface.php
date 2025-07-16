<?php

declare(strict_types=1);

namespace norsk\api\user\application;

use norsk\api\user\domain\model\Role;
use norsk\api\user\domain\valueObjects\UserName;


interface AuthenticatedUserInterface
{
    public function getUserName(): UserName;


    public function getRole(): Role;


    public function roleEquals(Role $role): bool;
}
