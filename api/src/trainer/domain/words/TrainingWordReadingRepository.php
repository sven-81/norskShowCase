<?php

declare(strict_types=1);

namespace norsk\api\trainer\domain\words;

use norsk\api\shared\domain\Vocabularies;
use norsk\api\user\domain\valueObjects\UserName;

interface TrainingWordReadingRepository
{
    public function getAllWordsFor(UserName $userName): Vocabularies;
}
