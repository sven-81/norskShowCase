<?php

declare(strict_types=1);

namespace norsk\api\trainer\application\wordTraining;

use norsk\api\trainer\application\wordTraining\useCases\SaveTrainedWord;
use norsk\api\trainer\domain\words\WordTrainingWritingRepository;

readonly class WordProgressUpdater
{
    public function __construct(private WordTrainingWritingRepository $wordRepository)
    {
    }


    public function handle(SaveTrainedWord $command): void
    {
        $this->wordRepository->saveAsTrainedWord($command->getUserName(), $command->getId());
    }
}
