<?php

declare(strict_types=1);

namespace norsk\api\user\domain\exceptions;

use InvalidArgumentException;
use norsk\api\shared\domain\DomainExceptionCode;
use norsk\api\shared\infrastructure\http\request\Parameter;

class ParameterMissingException extends InvalidArgumentException
{
    public function __construct(Parameter $parameter)
    {
        parent::__construct(
            'Missing required parameter: ' . $parameter->asString(),
            DomainExceptionCode::badRequest->value
        );
    }
}
