<?php

declare(strict_types=1);

namespace norsk\api\manager\application\verbManaging;

use norsk\api\manager\application\verbManaging\useCases\UpdateVerb;
use norsk\api\manager\domain\verbs\ManagedVerb;
use norsk\api\manager\domain\VocabularyUniquenessPolicy;
use norsk\api\manager\domain\WritingRepository;

readonly class VerbUpdater
{
    public function __construct(
        private WritingRepository $writer,
        private VocabularyUniquenessPolicy $policy
    ) {
    }


    public function handle(UpdateVerb $command): void
    {
        $id = $command->getId();
        $german = $command->getGerman();
        $norsk = $command->getNorsk();

        $this->policy->ensureVocabularyIsNotAlreadyPersisted($id, $german, $norsk);

        $managedVerb = ManagedVerb::fromPersistence(
            $id,
            $german,
            $norsk,
            $command->getNorskPresent(),
            $command->getNorskPast(),
            $command->getNorskPastPerfect()
        );

        $this->writer->update($managedVerb);
    }
}
