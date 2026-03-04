<?php

declare(strict_types=1);

namespace norsk\api\manager\application\verbManaging;

use norsk\api\manager\application\verbManaging\useCases\DeleteVerb;
use norsk\api\manager\domain\WritingRepository;
use norsk\api\shared\domain\VocabularyType;

readonly class VerbRemover
{
    public function __construct(
        private WritingRepository $writer,
    ) {
    }


    public function handle(DeleteVerb $command): void
    {
        $this->writer->remove($command->getId(), vocabularyType: VocabularyType::verb);
    }
}
