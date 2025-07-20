<?php

declare(strict_types=1);

namespace norsk\api\user\domain\port;

use norsk\api\user\domain\model\ValidatedUser;
use norsk\api\user\domain\valueObjects\InputPassword;
use norsk\api\user\domain\valueObjects\Pepper;
use norsk\api\user\domain\valueObjects\UserName;

interface UserReadingRepository
{
    public function getDataFor(UserName $userName, InputPassword $inputPassword, Pepper $pepper): ValidatedUser;


    public function checkIfUserExists(UserName $userName): void;


    public function isActiveManager(UserName $userName): void;
}