<?php

declare(strict_types=1);

namespace norsk\api\manager\domain\exceptions;

use DomainException;
use norsk\api\manager\domain\Identifier;
use norsk\api\shared\domain\VocabularyType;
use norsk\api\shared\infrastructure\http\response\ResponseCode;

class GermanRecordAlreadyInDatabaseException extends DomainException
{
    public function __construct(Identifier $identifier, VocabularyType $vocabularyType)
    {
        parent::__construct(
            'German ' . $vocabularyType->value . ' already exists for ' . $identifier->asMessageString(),
            ResponseCode::conflict->value
        );
    }
}
