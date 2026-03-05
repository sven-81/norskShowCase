<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\routing;

use norsk\api\manager\infrastructure\ManagerFactory;
use norsk\api\manager\infrastructure\web\controller\VerbManager;
use norsk\api\manager\infrastructure\web\controller\WordManager;
use norsk\api\trainer\infrastructure\TrainerFactory;
use norsk\api\trainer\infrastructure\web\controller\VerbTrainer;
use norsk\api\trainer\infrastructure\web\controller\WordTrainer;
use RuntimeException;

readonly class ControllerResolver
{
    public function __construct(
        private TrainerFactory $trainerFactory,
        private ManagerFactory $managerFactory
    ) {
    }


    public function resolve(ControllerName $name): object
    {
        return match ($name->asString()) {
            WordManager::class => $this->managerFactory->wordManager(),
            VerbManager::class => $this->managerFactory->verbManager(),
            WordTrainer::class => $this->trainerFactory->wordTrainer(),
            VerbTrainer::class => $this->trainerFactory->verbTrainer(),
            default => throw new RuntimeException('Unknown controller: ' . $name->asString()),
        };
    }
}
