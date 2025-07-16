<?php

declare(strict_types=1);

namespace norsk\api\manager\domain\exceptions;

use norsk\api\shared\domain\Id;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
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
