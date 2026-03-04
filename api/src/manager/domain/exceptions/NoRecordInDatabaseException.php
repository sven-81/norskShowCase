<?php

declare(strict_types=1);

namespace norsk\api\manager\domain\exceptions;

use norsk\api\shared\domain\DomainExceptionCode;
use norsk\api\shared\domain\Id;
use RuntimeException;

class NoRecordInDatabaseException extends RuntimeException
{
    public function __construct(Id $id)
    {
        parent::__construct(
            'No record found in database for id: ' . $id->asString(),
            DomainExceptionCode::notFound->value
        );
    }
}
