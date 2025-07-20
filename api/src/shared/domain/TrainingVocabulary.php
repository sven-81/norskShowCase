<?php

declare(strict_types=1);

namespace norsk\api\shared\domain;

use norsk\api\trainer\domain\SuccessCounter;

interface TrainingVocabulary extends Vocabulary
{

    public function getSuccessCounter(): SuccessCounter;

}
