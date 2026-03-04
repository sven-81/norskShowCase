<?php

declare(strict_types=1);

namespace norsk\api\user\domain\exceptions;

use InvalidArgumentException;
use norsk\api\shared\domain\DomainExceptionCode;

class CredentialsAreInvalidException extends InvalidArgumentException
{
    public function __construct()
    {
        parent::__construct(
            'Unauthorized: Cannot verify credentials',
            DomainExceptionCode::unauthorized->value
        );
    }
}
