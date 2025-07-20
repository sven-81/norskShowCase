<?php

declare(strict_types=1);

namespace norsk\api\shared\domain;

use norsk\api\infrastructure\persistence\AffectedRows;

interface ManagingVocabulary extends Vocabulary
{

    public function persistWith(VocabularyPersistencePort $writer): void;


    public function updateWith(VocabularyPersistencePort $writer): AffectedRows;
}
