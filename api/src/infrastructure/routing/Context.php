<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\routing;

use norsk\api\infrastructure\config\AppConfig;
use norsk\api\infrastructure\logging\Logger;
use norsk\api\infrastructure\persistence\DbConnection;
use norsk\api\manager\infrastructure\ManagerFactory;
use norsk\api\trainer\infrastructure\TrainerFactory;
use norsk\api\user\domain\service\JwtService;
use norsk\api\user\infrastructure\UserManagementFactory;

class Context
{
    public function __construct(
        private readonly Logger $logger,
        private readonly DbConnection $dbConnection,
        private readonly JwtService $jwtManagement,
        private readonly AppConfig $appConfig
    ) {
    }


    public function manager(): ManagerFactory
    {
        return new ManagerFactory($this->logger, $this->dbConnection, $this->appConfig);
    }


    public function trainer(): TrainerFactory
    {
        return new TrainerFactory($this->logger, $this->dbConnection, $this->appConfig);
    }


    public function userManagement(): UserManagementFactory
    {
        return new UserManagementFactory(
            $this->logger,
            $this->dbConnection,
            $this->jwtManagement,
            $this->appConfig
        );
    }
}
