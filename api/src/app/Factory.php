<?php

declare(strict_types=1);

namespace norsk\api\app;

use norsk\api\app\config\AppConfig;
use norsk\api\app\config\DbConfig;
use norsk\api\app\identityAccessManagement\IdentityAccessManagementFactory;
use norsk\api\app\logging\Logger;
use norsk\api\app\persistence\DbConnection;
use norsk\api\app\persistence\MysqliWrapper;
use norsk\api\app\routing\Context;
use norsk\api\app\routing\Router;
use norsk\api\user\UsersReader;
use Slim\Factory\AppFactory;

class Factory
{
    private Logger $logger;

    private DbConnection $dbConnection;


    private function __construct(private readonly AppConfig $appConfig, private readonly DbConfig $dbConfig)
    {
    }


    public static function fromConfigs(AppConfig $appConfig, DbConfig $dbConfig): self
    {
        return new self($appConfig, $dbConfig);
    }


    public function createNorskApi(): NorskApi
    {
        $this->logger = Logger::create($this->appConfig->getLogPath());
        $this->dbConnection = $this->createDbConnection($this->dbConfig);

        return new NorskApi(
            $this->logger,
            $this->createRouter(),
            AppFactory::create(),
            $this->appConfig->getAppLoggerConfig()
        );
    }


    private function createDbConnection(DbConfig $config): DbConnection
    {
        return new DbConnection($this->createMysqliWrapper(), $config);
    }


    private function createMysqliWrapper(): MysqliWrapper
    {
        return new MysqliWrapper();
    }


    private function createRouter(): Router
    {
        return new Router(
            $this->createIdentityAccessManagement(),
            $this->createContext(),
        );
    }


    private function createIdentityAccessManagement(): IdentityAccessManagementFactory
    {
        return new IdentityAccessManagementFactory(
            $this->appConfig,
            $this->logger,
            $this->createUsersReader(),
            $this->appConfig->getUrl()
        );
    }


    private function createUsersReader(): UsersReader
    {
        return new UsersReader($this->dbConnection);
    }


    private function createContext(): Context
    {
        return new Context(
            $this->logger,
            $this->dbConnection,
            $this->createIdentityAccessManagement()->createJwtManagement(),
            $this->appConfig
        );
    }
}
