<?php

declare(strict_types=1);

namespace norsk\api\user\domain\exceptions;

use InvalidArgumentException;
use norsk\api\shared\infrastructure\http\response\ResponseCode;

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
