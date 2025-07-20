<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\identityAccessManagement;

use norsk\api\infrastructure\config\AppConfig;
use norsk\api\infrastructure\logging\Logger;
use norsk\api\shared\infrastructure\http\response\Url;
use norsk\api\user\infrastructure\identityAccessManagement\authentication\Authentication;
use norsk\api\user\infrastructure\identityAccessManagement\authorization\Authorization;
use norsk\api\user\infrastructure\identityAccessManagement\jwt\JwtManagement;
use norsk\api\user\infrastructure\persistence\UsersReader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IdentityAccessManagementFactory::class)]
class IdentityAccessManagementFactoryTest extends TestCase
{
    private IdentityAccessManagementFactory $factory;


    protected function setUp(): void
    {
        $configMock = $this->createMock(AppConfig::class);
        $loggerMock = $this->createMock(Logger::class);
        $userReaderMock = $this->createMock(UsersReader::class);
        $url = Url::by('http://foo');
        $this->factory = new IdentityAccessManagementFactory($configMock, $loggerMock, $userReaderMock, $url);
    }


    public function testCanCreateTrainerAuthorization(): void
    {
        self::assertInstanceOf(Authorization::class, $this->factory->createTrainerAuthorization());
    }


    public function testCanCreateManagerAuthorization(): void
    {
        self::assertInstanceOf(Authorization::class, $this->factory->createManagerAuthorization());
    }


    public function testCanCreateAuthentication(): void
    {
        self::assertInstanceOf(Authentication::class, $this->factory->createAuthentication());
    }


    public function testCanCreateJwtManagement(): void
    {
        self::assertInstanceOf(JwtManagement::class, $this->factory->createJwtManagement());
    }
}
