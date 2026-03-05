<?php

declare(strict_types=1);

namespace norsk\api\trainer\domain\exceptions;

use norsk\api\shared\domain\DomainExceptionCode;
use RuntimeException;

class NoRecordInDatabaseException extends RuntimeException
{
    public function __construct(string $message)
    {
        parent::__construct($message, DomainExceptionCode::notFound->value);
    }
}
