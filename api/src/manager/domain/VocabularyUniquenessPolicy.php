<?php

declare(strict_types=1);

namespace norsk\api\manager\domain;

use norsk\api\shared\domain\German;
use norsk\api\shared\domain\Id;
use norsk\api\shared\domain\Norsk;

interface VocabularyUniquenessPolicy
{

    public function ensureVocabularyIsNotAlreadyPersisted(?Id $id, German $german, Norsk $norsk): void;

}
