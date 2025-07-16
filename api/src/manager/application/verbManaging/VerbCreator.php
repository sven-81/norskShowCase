<?php

declare(strict_types=1);

namespace norsk\api\manager\application\verbManaging;

use norsk\api\manager\application\verbManaging\useCases\CreateVerb;
use norsk\api\manager\domain\verbs\ManagedVerb;
use norsk\api\manager\domain\VocabularyUniquenessPolicy;
use norsk\api\manager\domain\WritingRepository;

readonly class VerbCreator
{
    public function __construct(
        private WritingRepository $writer,
        private VocabularyUniquenessPolicy $uniquenessPolicy,
    ) {
    }


    public function handle(CreateVerb $command): void
    {
        $german = $command->getGerman();
        $norsk = $command->getNorsk();
        $this->uniquenessPolicy->ensureVocabularyIsNotAlreadyPersisted(id: null, german: $german, norsk: $norsk);

        $managedVerb = ManagedVerb::createNew(
            $german,
            $norsk,
            $command->getNorskPresent(),
            $command->getNorskPast(),
            $command->getNorskPastPerfect()
        );

        $this->writer->add($managedVerb);
    }
}
