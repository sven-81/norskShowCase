<?php

declare(strict_types=1);

namespace norsk\api\manager\application\wordManaging;

use norsk\api\manager\application\wordManaging\useCases\GetAllWords;
use norsk\api\shared\domain\Vocabularies;
use norsk\api\trainer\domain\words\ManagingWordReadingRepository;

readonly class WordsProvider
{
    public function __construct(
        private ManagingWordReadingRepository $wordReader,
    ) {
    }


    public function handle(GetAllWords $command): Vocabularies
    {
        return $this->wordReader->getAllWords();
    }
}