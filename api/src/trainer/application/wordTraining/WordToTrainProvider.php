<?php

declare(strict_types=1);

namespace norsk\api\trainer\application\wordTraining;

use norsk\api\trainer\application\wordTraining\useCases\GetWordToTrain;
use norsk\api\trainer\domain\RandomGenerator;
use norsk\api\trainer\domain\words\TrainingWord;
use norsk\api\trainer\domain\words\TrainingWordReadingRepository;

class WordToTrainProvider
{
    public function __construct(
        private readonly TrainingWordReadingRepository $wordRepository,
        private readonly RandomGenerator $randomGenerator
    ) {
    }


    public function handle(GetWordToTrain $command): TrainingWord
    {
        $allWordsForUser = $this->wordRepository->getAllWordsFor($command->getUserName());

        return $this->randomGenerator->pickFrom($allWordsForUser);
    }
}
