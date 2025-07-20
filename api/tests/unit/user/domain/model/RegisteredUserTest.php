<?php

declare(strict_types=1);

namespace norsk\api\user;

use norsk\api\user\domain\model\RegisteredUser;
use norsk\api\user\domain\valueObjects\FirstName;
use norsk\api\user\domain\valueObjects\InputPassword;
use norsk\api\user\domain\valueObjects\LastName;
use norsk\api\user\domain\valueObjects\PasswordVector;
use norsk\api\user\domain\valueObjects\Pepper;
use norsk\api\user\domain\valueObjects\Salt;
use norsk\api\user\domain\valueObjects\UserName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RegisteredUser::class)]
class RegisteredUserTest extends TestCase
{
    private RegisteredUser $user;

    private UserName $userName;

    private FirstName $firstName;

    private LastName $lastName;

    private Salt $salt;


    protected function setUp(): void
    {
        $this->userName = UserName::by('someUserName');
        $this->firstName = FirstName::by('someFirstName');
        $this->lastName = LastName::by('someLastName');
        $inputPassword = InputPassword::by('someLoooongPassword');

        $passwordVectorMock = $this->createMock(PasswordVector::class);
        $passwordVectorMock->method('getPepper')
            ->willReturn(Pepper::by('iwwBYerIjfYhu04X0mm5GvN4woua6yqI'));
        $this->salt = Salt::by('8872879119a342e522c213e265f464b343a2d8156ab506936e7f3a80cc4f584c');
        $passwordVectorMock->method('getSalt')
            ->willReturn($this->salt);

        $this->user = RegisteredUser::create(
            $this->userName,
            $this->firstName,
            $this->lastName,
            $inputPassword,
            $passwordVectorMock
        );
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


    public function testCanGetPasswordHash(): void
    {
        self::assertStringStartsWith('$2y$10$', $this->user->getPasswordHash()->asHashString());
    }


    public function testCanGetSalt(): void
    {
        self::assertEquals($this->salt, $this->user->getSalt());
    }
}
