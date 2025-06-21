<?php

declare(strict_types=1);

namespace norsk\api\user;

enum Role: string
{
    case USER = 'user';
    case MANAGER = 'manager';
}
