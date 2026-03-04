<?php

declare(strict_types=1);

namespace norsk\api\manager\domain\exceptions;

use DomainException;
use norsk\api\manager\domain\Identifier;
use norsk\api\shared\domain\DomainExceptionCode;
use norsk\api\shared\domain\VocabularyType;

class RecordAlreadyInDatabaseException extends DomainException
{
    public function __construct(Identifier $identifier, VocabularyType $vocabularyType)
    {
        parent::__construct(
            ucfirst($vocabularyType->value) . ' already exists for ' . $identifier->asMessageString(),
            DomainExceptionCode::conflict->value
        );
    }
}
