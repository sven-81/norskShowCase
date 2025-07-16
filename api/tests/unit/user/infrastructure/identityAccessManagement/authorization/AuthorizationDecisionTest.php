<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\identityAccessManagement\authorization;

use norsk\api\user\domain\model\Role;
use norsk\api\user\domain\valueObjects\UserName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AuthorizationDecision::class)]
class AuthorizationDecisionTest extends TestCase
{
    private AuthorizationDecision $authorizationDecision;

    private bool $isAuthorized;

    private UserName $userName;

    private Role $role;

    private AuthorizationDecision $emptyAuthorizationDecision;


    protected function setUp(): void
    {
        $this->isAuthorized = true;
        $this->userName = UserName::by('some User');
        $this->role = Role::MANAGER;

        $this->authorizationDecision = AuthorizationDecision::by(
            $this->isAuthorized,
            $this->userName,
            $this->role
        );

        $this->emptyAuthorizationDecision = AuthorizationDecision::by();
    }


    public function testCanGetUserName(): void
    {
        self::assertEquals($this->userName, $this->authorizationDecision->getUserName());
    }


    public function testCanGetUserNameAsNull(): void
    {
        self::assertNull($this->emptyAuthorizationDecision->getUserName());
    }


    public function testCanGetRole(): void
    {
        self::assertEquals($this->role, $this->authorizationDecision->getRole());
    }


    public function testCanGetRoleIfNull(): void
    {
        self::assertNull($this->emptyAuthorizationDecision->getRole());
    }


    public function testIsAuthorized(): void
    {
        self::assertEquals($this->isAuthorized, $this->authorizationDecision->isAuthorized());
    }


    public function testIsFailed(): void
    {
        self::assertTrue($this->emptyAuthorizationDecision->failed());
    }
}
