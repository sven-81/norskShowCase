<?php

declare(strict_types=1);

namespace norsk\api\user\domain\port;

use norsk\api\user\domain\model\RegisteredUser;

interface UserWritingRepository
{
    public function add(RegisteredUser $user): void;
}