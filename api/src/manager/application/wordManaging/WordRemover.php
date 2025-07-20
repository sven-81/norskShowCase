<?php

declare(strict_types=1);

namespace norsk\api\manager\application\wordManaging;

use norsk\api\manager\application\wordManaging\useCases\DeleteWord;
use norsk\api\manager\domain\WritingRepository;
use norsk\api\shared\domain\VocabularyType;

readonly class WordRemover
{
    public function __construct(
        private WritingRepository $writer,
    ) {
    }


    public function handle(DeleteWord $command): void
    {
        $this->writer->remove($command->getId(), vocabularyType: VocabularyType::word);
    }
}
