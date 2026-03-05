<?php

declare(strict_types=1);

namespace norsk\api\trainer\application\wordTraining;

use norsk\api\shared\domain\TrainingVocabulary;
use norsk\api\trainer\application\wordTraining\useCases\GetWordToTrain;
use norsk\api\trainer\domain\RandomGenerator;
use norsk\api\trainer\domain\words\TrainingWordReadingRepository;

readonly class WordToTrainProvider
{
    public function __construct(
        private TrainingWordReadingRepository $wordRepository,
        private RandomGenerator $randomGenerator
    ) {
    }


    public function handle(GetWordToTrain $command): TrainingVocabulary
    {
        $allWordsForUser = $this->wordRepository->getAllWordsFor($command->getUserName());

        return $this->randomGenerator->pickFrom($allWordsForUser);
    }
}
