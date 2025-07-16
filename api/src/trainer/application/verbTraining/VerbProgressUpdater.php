<?php

declare(strict_types=1);

namespace norsk\api\trainer\application\verbTraining;

use norsk\api\trainer\application\verbTraining\useCases\SaveTrainedVerb;
use norsk\api\trainer\domain\WritingRepository;

class VerbProgressUpdater
{
    public function __construct(private readonly WritingRepository $verbRepository)
    {
    }


    public function handle(SaveTrainedVerb $command): void
    {
        $this->verbRepository->saveAsTrainedVerb($command->getUserName(), $command->getId());
    }
}
