<?php

declare(strict_types=1);

namespace norsk\api\trainer\domain\verbs;

use norsk\api\shared\domain\Vocabularies;

interface ManagingVerbReadingRepository
{

    public function getAllVerbs(): Vocabularies;
}
