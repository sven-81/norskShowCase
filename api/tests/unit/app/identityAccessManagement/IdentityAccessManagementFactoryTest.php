<?php

declare(strict_types=1);

namespace norsk\api\app\identityAccessManagement;

use norsk\api\app\config\AppConfig;
use norsk\api\app\logging\Logger;
use norsk\api\app\response\Url;
use norsk\api\user\UsersReader;
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


    public function testCanCreateAuthorization(): void
    {
        self::assertInstanceOf(Authorization::class, $this->factory->createAuthorization());
    }


    public function testCanCreateAuthentication(): void
    {
        $sessionMock = $this->createMock(Session::class);
        self::assertInstanceOf(Authentication::class, $this->factory->createAuthentication($sessionMock));
    }


    public function testCanCreateJwtManagement(): void
    {
        self::assertInstanceOf(JwtManagement::class, $this->factory->createJwtManagement());
    }
}
