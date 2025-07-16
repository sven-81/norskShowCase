<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure;

use norsk\api\infrastructure\config\AppConfig;
use norsk\api\infrastructure\logging\Logger;
use norsk\api\infrastructure\persistence\DbConnection;
use norsk\api\user\infrastructure\identityAccessManagement\jwt\JwtManagement;
use norsk\api\user\infrastructure\web\controller\Login;
use norsk\api\user\infrastructure\web\controller\Registration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserManagementFactory::class)]
class UserManagementFactoryTest extends TestCase
{
    private UserManagementFactory $factory;


    protected function setUp(): void
    {
        $this->factory = new UserManagementFactory(
            $this->createMock(Logger::class),
            $this->createMock(DbConnection::class),
            $this->createMock(JwtManagement::class),
            $this->createMock(AppConfig::class)
        );
    }


    public function testCreatesLogin(): void
    {
        $login = $this->factory->login();

        $this->assertInstanceOf(Login::class, $login);
    }


    public function testCreatesRegistration(): void
    {
        $registration = $this->factory->registration();

        $this->assertInstanceOf(Registration::class, $registration);
    }
}
