<?php

declare(strict_types=1);

namespace norsk\api\trainer\domain\verbs;

use norsk\api\shared\domain\Vocabularies;
use norsk\api\user\domain\valueObjects\UserName;

interface TrainingVerbReadingRepository
{
    public function getAllVerbsFor(UserName $userName): Vocabularies;

}
