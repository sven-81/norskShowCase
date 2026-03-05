<?php

declare(strict_types=1);

namespace norsk\api\trainer\application\verbTraining;

use norsk\api\shared\domain\TrainingVocabulary;
use norsk\api\trainer\application\verbTraining\useCases\GetVerbToTrain;
use norsk\api\trainer\domain\RandomGenerator;
use norsk\api\trainer\domain\verbs\TrainingVerbReadingRepository;

readonly class VerbToTrainProvider
{
    public function __construct(
        private TrainingVerbReadingRepository $verbRepository,
        private RandomGenerator $randomGenerator
    ) {
    }


    public function handle(GetVerbToTrain $command): TrainingVocabulary
    {
        $allVerbsForUser = $this->verbRepository->getAllVerbsFor($command->getUserName());

        return $this->randomGenerator->pickFrom($allVerbsForUser);
    }
}
