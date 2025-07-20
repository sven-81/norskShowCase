<?php

declare(strict_types=1);

namespace norsk\api\shared\domain;

use norsk\api\infrastructure\persistence\AffectedRows;
use norsk\api\manager\domain\verbs\ManagedVerb;
use norsk\api\manager\domain\words\ManagedWord;

interface VocabularyPersistencePort
{
    public function saveNewWord(ManagedWord $word): void;


    public function saveEditedWord(ManagedWord $word): AffectedRows;


    public function saveNewVerb(ManagedVerb $verb): void;


    public function saveEditedVerb(ManagedVerb $verb): AffectedRows;
}

