<?php

declare(strict_types=1);

namespace norsk\api\trainer\domain\words;

interface ManagingWordReadingRepository
{
    public function getAllWords();
}
