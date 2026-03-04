<?php

declare(strict_types=1);

namespace norsk\api\manager\application\wordManaging;

use norsk\api\manager\application\wordManaging\useCases\CreateWord;
use norsk\api\manager\domain\VocabularyUniquenessPolicy;
use norsk\api\manager\domain\words\ManagedWord;
use norsk\api\manager\domain\WritingRepository;

readonly class WordCreator
{
    public function __construct(
        private WritingRepository $writer,
        private VocabularyUniquenessPolicy $uniquenessPolicy,
    ) {
    }


    public function handle(CreateWord $command): void
    {
        $german = $command->getGerman();
        $norsk = $command->getNorsk();
        $this->uniquenessPolicy->ensureVocabularyIsNotAlreadyPersisted(id: null, german: $german, norsk: $norsk);

        $managedWord = ManagedWord::createNew($german, $norsk);

        $this->writer->add($managedWord);
    }
}
