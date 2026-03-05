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
        $loggerStub = $this->createStub(Logger::class);
        $dbConnectionStub = $this->createStub(DbConnection::class);
        $jwtManagementStub = $this->createStub(JwtManagement::class);
        $appConfigStub = $this->createStub(AppConfig::class);
        $this->context = new Context($loggerStub, $dbConnectionStub, $jwtManagementStub, $appConfigStub);
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
