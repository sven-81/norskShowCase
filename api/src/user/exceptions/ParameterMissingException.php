<?php

declare(strict_types=1);

namespace norsk\api\user\exceptions;

use InvalidArgumentException;
use norsk\api\app\request\Parameter;
use norsk\api\app\response\ResponseCode;

class ParameterMissingException extends InvalidArgumentException
{
    public function __construct(Parameter $parameter)
    {
        parent::__construct(
            'Missing required parameter: ' . $parameter->asString(),
            ResponseCode::badRequest->value
        );
    }
}
