<?php

declare(strict_types=1);

namespace norsk\api\manager\exceptions;

use norsk\api\app\response\ResponseCode;
use norsk\api\shared\Id;
use RuntimeException;

class NoRecordInDatabaseException extends RuntimeException
{
    public function __construct(Id $id)
    {
        parent::__construct(
            'No record found in database for id: ' . $id->asString(),
            ResponseCode::notFound->value
        );
    }
}
