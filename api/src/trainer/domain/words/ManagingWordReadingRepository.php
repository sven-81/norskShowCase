<?php

declare(strict_types=1);

namespace norsk\api\trainer\domain\words;

use norsk\api\manager\domain\ManagedVocabularies;

interface ManagingWordReadingRepository
{
    public function getAllWords(): ManagedVocabularies;
}
