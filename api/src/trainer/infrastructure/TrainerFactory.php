<?php

declare(strict_types=1);

namespace norsk\api\trainer\infrastructure;

use norsk\api\infrastructure\config\AppConfig;
use norsk\api\infrastructure\logging\Logger;
use norsk\api\infrastructure\persistence\DbConnection;
use norsk\api\trainer\application\verbTraining\VerbProgressUpdater;
use norsk\api\trainer\application\verbTraining\VerbToTrainProvider;
use norsk\api\trainer\application\wordTraining\WordProgressUpdater;
use norsk\api\trainer\application\wordTraining\WordToTrainProvider;
use norsk\api\trainer\domain\RandomGenerator;
use norsk\api\trainer\domain\RandomNumber;
use norsk\api\trainer\infrastructure\persistence\TrainingWriter;
use norsk\api\trainer\infrastructure\persistence\VerbTrainingReader;
use norsk\api\trainer\infrastructure\persistence\WordTrainingReader;
use norsk\api\trainer\infrastructure\web\controller\VerbTrainer;
use norsk\api\trainer\infrastructure\web\controller\WordTrainer;

class TrainerFactory
{
    public function __construct(
        private readonly Logger $logger,
        private readonly DbConnection $dbConnection,
        private readonly AppConfig $appConfig
    ) {
    }


    public function wordTrainer(): WordTrainer
    {
        $reader = new WordTrainingReader($this->dbConnection);
        $writer = new TrainingWriter($this->dbConnection);
        $random = new RandomGenerator(RandomNumber::create());

        return new WordTrainer(
            $this->logger,
            new WordToTrainProvider($reader, $random),
            new WordProgressUpdater($writer),
            $this->appConfig->getUrl()
        );
    }


    public function verbTrainer(): VerbTrainer
    {
        $reader = new VerbTrainingReader($this->dbConnection);
        $writer = new TrainingWriter($this->dbConnection);
        $random = new RandomGenerator(RandomNumber::create());

        return new VerbTrainer(
            $this->logger,
            new VerbToTrainProvider($reader, $random),
            new VerbProgressUpdater($writer),
            $this->appConfig->getUrl()
        );
    }
}
