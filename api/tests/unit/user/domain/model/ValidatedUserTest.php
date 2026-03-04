<?php

declare(strict_types=1);

namespace norsk\api\user;

use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\user\domain\exceptions\CredentialsAreInvalidException;
use norsk\api\user\domain\exceptions\NoActiveUserException;
use norsk\api\user\domain\model\Role;
use norsk\api\user\domain\model\UserData;
use norsk\api\user\domain\model\ValidatedUser;
use norsk\api\user\domain\valueObjects\FirstName;
use norsk\api\user\domain\valueObjects\InputPassword;
use norsk\api\user\domain\valueObjects\LastName;
use norsk\api\user\domain\valueObjects\PasswordHash;
use norsk\api\user\domain\valueObjects\PasswordVector;
use norsk\api\user\domain\valueObjects\Pepper;
use norsk\api\user\domain\valueObjects\Salt;
use norsk\api\user\domain\valueObjects\UserName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ValidatedUser::class)]
class ValidatedUserTest extends TestCase
{
    private ValidatedUser $user;

    private UserName $userName;

    private FirstName $firstName;

    private LastName $lastName;

    private InputPassword $givenPassword;

    private Pepper $pepper;

    private Salt $salt;

    private PasswordHash $hash;

    private UserData $userData;


    protected function setUp(): void
    {
        $this->userName      = UserName::by('someUserName');
        $this->firstName     = FirstName::by('someFirstName');
        $this->lastName      = LastName::by('someLastName');
        $this->pepper        = Pepper::by('iwwBYerIjfYhu04X0mm5GvN4woua6yqI');
        $this->salt          = Salt::by('b681dc56924c1b5dc92bf97f79708fd89e84cbe128548687bb8070eb002e82b4');
        $this->givenPassword = InputPassword::by('someLoooongPassword');

        $vector     = PasswordVector::by($this->salt, $this->pepper);
        $this->hash = PasswordHash::hashBy($this->givenPassword, $vector);

        $this->userData = UserData::of(
            userName: $this->userName,
            firstName: $this->firstName,
            lastName: $this->lastName,
            passwordHash: $this->hash,
            salt: $this->salt,
            role: Role::USER,
            isActive: true,
        );

        $this->user = ValidatedUser::fromUserData($this->userData, $this->givenPassword, $this->pepper);
    }


    public function testCanGetUserName(): void
    {
        self::assertEquals($this->userName, $this->user->getUserName());
    }


    public function testCanGetFirstName(): void
    {
        self::assertEquals($this->firstName, $this->user->getFirstName());
    }


    public function testCanGetLastName(): void
    {
        self::assertEquals($this->lastName, $this->user->getLastName());
    }


    public function testCanGetRole(): void
    {
        self::assertSame(Role::USER, $this->user->getRole());
    }


    public function testThrowsExceptionOnInvalidPassword(): void
    {
        $this->expectException(CredentialsAreInvalidException::class);

        ValidatedUser::fromUserData(
            $this->userData,
            InputPassword::by('somethingVeryWrong!!'),
            $this->pepper
        );
    }


    public function testThrowsExceptionIfUserIsNotActive(): void
    {
        $this->expectExceptionObject(
            new NoActiveUserException('Forbidden: user is not active', ResponseCode::forbidden->value)
        );

        $inactiveUserData = UserData::of(
            userName: $this->userName,
            firstName: $this->firstName,
            lastName: $this->lastName,
            passwordHash: $this->hash,
            salt: $this->salt,
            role: Role::USER,
            isActive: false,
        );

        ValidatedUser::fromUserData($inactiveUserData, $this->givenPassword, $this->pepper);
    }
}
