<?php

declare(strict_types=1);

namespace norsk\api\trainer\domain\verbs;

use norsk\api\shared\domain\Id;
use norsk\api\user\domain\valueObjects\UserName;

interface VerbTrainingWritingRepository
{
    public function saveAsTrainedVerb(UserName $userName, Id $id): void;
}
