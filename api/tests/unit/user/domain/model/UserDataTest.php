<?php

declare(strict_types=1);

namespace norsk\api\user\domain\model;

use norsk\api\user\domain\valueObjects\FirstName;
use norsk\api\user\domain\valueObjects\LastName;
use norsk\api\user\domain\valueObjects\PasswordHash;
use norsk\api\user\domain\valueObjects\Salt;
use norsk\api\user\domain\valueObjects\UserName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserData::class)]
class UserDataTest extends TestCase
{
    private UserData $userData;

    private UserName $userName;

    private FirstName $firstName;

    private LastName $lastName;

    private Role $role;


    protected function setUp(): void
    {
        $this->userName = UserName::by('someUser');
        $this->firstName = FirstName::by('Max');
        $this->lastName = LastName::by('Muster');
        $this->role = Role::USER;

        $saltStub = $this->createStub(Salt::class);
        $hashStub = $this->createStub(PasswordHash::class);

        $this->userData = UserData::of(
            userName: $this->userName,
            firstName: $this->firstName,
            lastName: $this->lastName,
            passwordHash: $hashStub,
            salt: $saltStub,
            role: $this->role,
            isActive: true,
        );
    }


    public function testHasUserName(): void
    {
        self::assertSame($this->userName, $this->userData->userName);
    }


    public function testHasFirstName(): void
    {
        self::assertSame($this->firstName, $this->userData->firstName);
    }


    public function testHasLastName(): void
    {
        self::assertSame($this->lastName, $this->userData->lastName);
    }


    public function testHasRole(): void
    {
        self::assertSame($this->role, $this->userData->role);
    }


    public function testHasIsActive(): void
    {
        self::assertTrue($this->userData->isActive);
    }


    public function testIsNotActiveWhenFlagIsFalse(): void
    {
        $saltStub = $this->createStub(Salt::class);
        $hashStub = $this->createStub(PasswordHash::class);

        $inactiveUserData = UserData::of(
            userName: $this->userName,
            firstName: $this->firstName,
            lastName: $this->lastName,
            passwordHash: $hashStub,
            salt: $saltStub,
            role: $this->role,
            isActive: false,
        );

        self::assertFalse($inactiveUserData->isActive);
    }
}

