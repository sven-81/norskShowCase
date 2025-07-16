<?php

declare(strict_types=1);

namespace norsk\api\infrastructure;

use norsk\api\infrastructure\config\AppConfig;
use norsk\api\infrastructure\config\DbConfig;
use norsk\api\infrastructure\logging\Logger;
use norsk\api\infrastructure\persistence\DbConnection;
use norsk\api\infrastructure\persistence\MysqliWrapper;
use norsk\api\infrastructure\routing\Context;
use norsk\api\infrastructure\routing\ControllerResolver;
use norsk\api\infrastructure\routing\CorsMiddleware;
use norsk\api\infrastructure\routing\Router;
use norsk\api\user\infrastructure\identityAccessManagement\IdentityAccessManagementFactory;
use norsk\api\user\infrastructure\persistence\UsersReader;
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
            $this->createCorsMiddleware(),
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
            new ControllerResolver($this->createContext()->trainer(), $this->createContext()->manager()),
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


    private function createCorsMiddleware(): CorsMiddleware
    {
        return new CorsMiddleware($this->appConfig->getUrl());
    }
}
