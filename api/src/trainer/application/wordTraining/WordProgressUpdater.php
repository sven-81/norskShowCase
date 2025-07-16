<?php

declare(strict_types=1);

namespace norsk\api\trainer\application\wordTraining;

use norsk\api\trainer\application\wordTraining\useCases\SaveTrainedWord;
use norsk\api\trainer\domain\WritingRepository;

class WordProgressUpdater
{
    public function __construct(private readonly WritingRepository $verbRepository)
    {
    }


    public function handle(SaveTrainedWord $command): void
    {
        $this->verbRepository->saveAsTrainedWord($command->getUserName(), $command->getId());
    }
}
