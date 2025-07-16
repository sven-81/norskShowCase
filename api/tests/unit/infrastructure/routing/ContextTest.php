<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\routing;

use norsk\api\infrastructure\config\AppConfig;
use norsk\api\infrastructure\logging\Logger;
use norsk\api\infrastructure\persistence\DbConnection;
use norsk\api\manager\infrastructure\ManagerFactory;
use norsk\api\trainer\infrastructure\TrainerFactory;
use norsk\api\user\infrastructure\identityAccessManagement\jwt\JwtManagement;
use norsk\api\user\infrastructure\UserManagementFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Context::class)]
class ContextTest extends TestCase
{
    private Context $context;


    protected function setUp(): void
    {
        $loggerMock = $this->createMock(Logger::class);
        $dbConnectionMock = $this->createMock(DbConnection::class);
        $jwtManagementMock = $this->createMock(JwtManagement::class);
        $appConfigMock = $this->createMock(AppConfig::class);
        $this->context = new Context($loggerMock, $dbConnectionMock, $jwtManagementMock, $appConfigMock);
    }


    public function testCanCreateUserManagementFactory(): void
    {
        self::assertInstanceOf(UserManagementFactory::class, $this->context->userManagement());
    }


    public function testCanCreateTrainerFactory(): void
    {
        self::assertInstanceOf(TrainerFactory::class, $this->context->trainer());
    }


    public function testCanCreateManagerFactory(): void
    {
        self::assertInstanceOf(ManagerFactory::class, $this->context->manager());
    }
}
