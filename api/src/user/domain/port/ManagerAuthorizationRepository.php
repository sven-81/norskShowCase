<?php

declare(strict_types=1);

namespace norsk\api\user\domain\port;

use norsk\api\user\domain\valueObjects\UserName;

interface ManagerAuthorizationRepository
{
    public function isActiveManager(UserName $userName): void;
}

