<?php

declare(strict_types=1);

namespace norsk\api\user\domain\port;

use norsk\api\user\domain\model\UserData;
use norsk\api\user\domain\valueObjects\UserName;

interface UserReadingRepository
{
    public function findByUserName(UserName $userName): UserData;


    public function checkIfUserExists(UserName $userName): void;
}
