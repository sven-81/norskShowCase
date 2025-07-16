<?php

declare(strict_types=1);

namespace norsk\api\trainer\domain;

use norsk\api\shared\domain\Id;
use norsk\api\user\domain\valueObjects\UserName;

interface WritingRepository
{
    public function saveAsTrainedWord(UserName $userName, Id $id): void;

    public function saveAsTrainedVerb(UserName $userName, Id $id): void;
}
