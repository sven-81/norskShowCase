<?php

declare(strict_types=1);

namespace norsk\api\trainer\domain\verbs;

use norsk\api\manager\domain\ManagedVocabularies;

interface ManagingVerbReadingRepository
{
    public function getAllVerbs(): ManagedVocabularies;
}
