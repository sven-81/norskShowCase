<?php

declare(strict_types=1);

namespace norsk\api\app\routing;

use norsk\api\app\config\AppConfig;
use norsk\api\app\identityAccessManagement\JwtManagement;
use norsk\api\app\logging\Logger;
use norsk\api\app\persistence\DbConnection;
use norsk\api\manager\verbs\VerbManager;
use norsk\api\manager\words\WordManager;
use norsk\api\trainer\verbs\VerbTrainer;
use norsk\api\trainer\words\WordTrainer;
use norsk\api\user\Login;
use norsk\api\user\Registration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Context::class)]
class ContextTest extends TestCase
{
    private Context $context;


    public function testCanCreateLogin(): void
    {
        self::assertInstanceOf(Login::class, $this->context->login());
    }


    public function testCanCreateRegistration(): void
    {
        self::assertInstanceOf(Registration::class, $this->context->registration());
    }


    public function testCanCreateWordTrainer(): void
    {
        self::assertInstanceOf(WordTrainer::class, $this->context->wordTrainer());
    }


    public function testCanCreateVerbTrainer(): void
    {
        self::assertInstanceOf(VerbTrainer::class, $this->context->verbTrainer());
    }


    public function testCanCreateWordManager(): void
    {
        self::assertInstanceOf(WordManager::class, $this->context->wordManager());
    }


    public function testCanCreateVerbManager(): void
    {
        self::assertInstanceOf(VerbManager::class, $this->context->verbManager());
    }


    protected function setUp(): void
    {
        $loggerMock = $this->createMock(Logger::class);
        $dbConnectionMock = $this->createMock(DbConnection::class);
        $jwtManagementMock = $this->createMock(JwtManagement::class);
        $appConfigMock = $this->createMock(AppConfig::class);
        $this->context = new Context($loggerMock, $dbConnectionMock, $jwtManagementMock, $appConfigMock);
    }
}
