<?php

declare(strict_types=1);

namespace norsk\api\manager\application\wordManaging;

use norsk\api\manager\application\wordManaging\useCases\UpdateWord;
use norsk\api\manager\domain\VocabularyUniquenessPolicy;
use norsk\api\manager\domain\words\ManagedWord;
use norsk\api\manager\domain\WritingRepository;

readonly class WordUpdater
{
    public function __construct(
        private WritingRepository $writer,
        private VocabularyUniquenessPolicy $policy
    ) {
    }


    public function handle(UpdateWord $command): void
    {
        $id = $command->getId();
        $german = $command->getGerman();
        $norsk = $command->getNorsk();

        $this->policy->ensureVocabularyIsNotAlreadyPersisted($id, $german, $norsk);

        $managedWord = ManagedWord::fromPersistence(
            $id,
            $german,
            $norsk
        );

        $this->writer->update($managedWord);
    }
}
