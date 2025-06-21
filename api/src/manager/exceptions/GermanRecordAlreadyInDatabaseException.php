<?php

declare(strict_types=1);

namespace norsk\api\manager\exceptions;

use norsk\api\app\response\ResponseCode;
use norsk\api\manager\Identifier;
use norsk\api\shared\VocabularyType;
use RuntimeException;

class GermanRecordAlreadyInDatabaseException extends RuntimeException
{
    public function __construct(Identifier $identifier, VocabularyType $vocabularyType)
    {
        parent::__construct(
            'German ' . $vocabularyType->value . ' already exists for ' . $identifier->asMessageString(),
            ResponseCode::conflict->value
        );
    }
}
