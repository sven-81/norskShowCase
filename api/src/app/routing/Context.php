<?php

declare(strict_types=1);

namespace norsk\api\app\routing;

use norsk\api\app\config\AppConfig;
use norsk\api\app\identityAccessManagement\JwtManagement;
use norsk\api\app\logging\Logger;
use norsk\api\app\persistence\DbConnection;
use norsk\api\manager\ManagerWriter;
use norsk\api\manager\verbs\VerbManager;
use norsk\api\manager\verbs\VerbReader as VerbManagerReader;
use norsk\api\manager\words\WordManager;
use norsk\api\manager\words\WordReader;
use norsk\api\trainer\RandomGenerator;
use norsk\api\trainer\RandomNumber;
use norsk\api\trainer\TrainingWriter;
use norsk\api\trainer\verbs\VerbReader as VerbTrainingReader;
use norsk\api\trainer\verbs\VerbTrainer;
use norsk\api\trainer\words\WordReader as WordTrainingReader;
use norsk\api\trainer\words\WordTrainer;
use norsk\api\user\Login;
use norsk\api\user\PasswordVector;
use norsk\api\user\Pepper;
use norsk\api\user\Registration;
use norsk\api\user\Salt;
use norsk\api\user\UsersReader;
use norsk\api\user\UsersWriter;

class Context
{
    public function __construct(
        private readonly Logger $logger,
        private readonly DbConnection $dbConnection,
        private readonly JwtManagement $jwtManagement,
        private readonly AppConfig $appConfig
    ) {
    }


    public function login(): Login
    {
        $usersReader = $this->createUsersReader();

        return new Login(
            $this->logger,
            $usersReader,
            $this->jwtManagement,
            $this->getPepper(),
            $this->appConfig->getUrl()
        );
    }


    private function createUsersReader(): UsersReader
    {
        return new UsersReader($this->dbConnection);
    }


    private function getPepper(): Pepper
    {
        return $this->appConfig->getPepper();
    }


    public function registration(): Registration
    {
        $passwordVector = PasswordVector::by(Salt::init(), $this->getPepper());
        $usersWriter = new UsersWriter($this->dbConnection);

        return new Registration($this->logger, $usersWriter, $passwordVector, $this->appConfig->getUrl());
    }


    public function wordTrainer(): WordTrainer
    {
        $randomGenerator = new RandomGenerator(RandomNumber::create());
        $trainingWordReader = new WordTrainingReader($this->dbConnection);
        $trainingWriter = new TrainingWriter($this->dbConnection);

        return new WordTrainer(
            $this->logger,
            $randomGenerator,
            $trainingWordReader,
            $trainingWriter,
            $this->appConfig->getUrl()
        );
    }


    public function verbTrainer(): VerbTrainer
    {
        $randomGenerator = new RandomGenerator(RandomNumber::create());
        $trainingVerbReader = new VerbTrainingReader($this->dbConnection);
        $trainingWriter = new TrainingWriter($this->dbConnection);

        return new VerbTrainer(
            $this->logger,
            $randomGenerator,
            $trainingVerbReader,
            $trainingWriter,
            $this->appConfig->getUrl()
        );
    }


    public function wordManager(): WordManager
    {
        $wordReader = new WordReader($this->dbConnection);
        $wordWriter = new ManagerWriter($this->dbConnection);

        return new WordManager($this->logger, $wordReader, $wordWriter, $this->appConfig->getUrl());
    }


    public function verbManager(): VerbManager
    {
        $verbReader = new VerbManagerReader($this->dbConnection);
        $verbWriter = new ManagerWriter($this->dbConnection);

        return new VerbManager($this->logger, $verbReader, $verbWriter, $this->appConfig->getUrl());
    }
}
