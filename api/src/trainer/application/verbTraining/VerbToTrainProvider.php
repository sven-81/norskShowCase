<?php

declare(strict_types=1);

namespace norsk\api\trainer\application\verbTraining;

use norsk\api\trainer\application\verbTraining\useCases\GetVerbToTrain;
use norsk\api\trainer\domain\RandomGenerator;
use norsk\api\trainer\domain\verbs\TrainingVerb;
use norsk\api\trainer\domain\verbs\TrainingVerbReadingRepository;

class VerbToTrainProvider
{
    public function __construct(
        private readonly TrainingVerbReadingRepository $verbRepository,
        private readonly RandomGenerator $randomGenerator
    ) {
    }


    public function handle(GetVerbToTrain $command): TrainingVerb
    {
        $allVerbsForUser = $this->verbRepository->getAllVerbsFor($command->getUserName());

        return $this->randomGenerator->pickFrom($allVerbsForUser);
    }
}
