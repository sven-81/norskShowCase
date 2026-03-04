<?php

declare(strict_types=1);

namespace norsk\api\user\domain\model;

use norsk\api\user\domain\valueObjects\UserName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AuthorizationResult::class)]
class AuthorizationResultTest extends TestCase
{
    private UserName $userName;

    private Role $role;


    protected function setUp(): void
    {
        $this->userName = UserName::by('someUser');
        $this->role = Role::MANAGER;
    }


    public function testGrantedIsAuthorized(): void
    {
        $result = AuthorizationResult::granted($this->userName, $this->role);

        self::assertTrue($result->isAuthorized());
        self::assertFalse($result->wasDenied());
    }


    public function testGrantedHasUserNameAndRole(): void
    {
        $result = AuthorizationResult::granted($this->userName, $this->role);

        self::assertEquals($this->userName, $result->getUserName());
        self::assertEquals($this->role, $result->getRole());
    }


    public function testDeniedIsNotAuthorized(): void
    {
        $result = AuthorizationResult::denied();

        self::assertFalse($result->isAuthorized());
        self::assertTrue($result->wasDenied());
    }


    public function testDeniedHasNullUserNameAndRole(): void
    {
        $result = AuthorizationResult::denied();

        self::assertNull($result->getUserName());
        self::assertNull($result->getRole());
    }
}

