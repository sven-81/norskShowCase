<?php

declare(strict_types=1);

namespace norsk\api\trainer\domain\words;

use norsk\api\shared\domain\Id;
use norsk\api\user\domain\valueObjects\UserName;

interface WordTrainingWritingRepository
{
    public function saveAsTrainedWord(UserName $userName, Id $id): void;
}

