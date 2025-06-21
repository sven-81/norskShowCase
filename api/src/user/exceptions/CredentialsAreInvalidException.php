<?php

declare(strict_types=1);

namespace norsk\api\user\exceptions;

use InvalidArgumentException;
use norsk\api\app\response\ResponseCode;

class CredentialsAreInvalidException extends InvalidArgumentException
{
    public function __construct()
    {
        parent::__construct(
            'Unauthorized: Cannot verify credentials',
            ResponseCode::unauthorized->value
        );
    }
}
