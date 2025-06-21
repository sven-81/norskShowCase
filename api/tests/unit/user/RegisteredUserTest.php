<?php

declare(strict_types=1);

namespace norsk\api\user;

use InvalidArgumentException;
use norsk\api\app\request\Payload;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(RegisteredUser::class)]
class RegisteredUserTest extends TestCase
{
    private RegisteredUser $user;

    private UserName $userName;

    private FirstName $firstName;

    private LastName $lastName;

    private array $payload;

    private Salt $salt;

    private MockObject|PasswordVector $passwordVectorMock;


    public static function getMissingKeys(): array
    {
        return [
            'username' => ['username'],
            'firstName' => ['firstName'],
            'lastName' => ['lastName'],
            'password' => ['password'],
        ];
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


    #[DataProvider('getMissingKeys')]
    public function testThrowsExceptionIfParameterIsMissing(string $missingKey): void
    {
        $this->expectExceptionObject(new InvalidArgumentException($missingKey, 400));

        unset($this->payload[$missingKey]);

        $invalidPayloadMock = $this->createMock(Payload::class);
        $invalidPayloadMock->method('asArray')
            ->willReturn($this->payload);

        $this->user = RegisteredUser::createByPayload($invalidPayloadMock, $this->passwordVectorMock);
    }


    protected function setUp(): void
    {
        $this->userName = UserName::by('someUserName');
        $this->firstName = FirstName::by('someFirstName');
        $this->lastName = LastName::by('someLastName');

        $this->passwordVectorMock = $this->createMock(PasswordVector::class);
        $this->passwordVectorMock->method('getPepper')
            ->willReturn(Pepper::by('iwwBYerIjfYhu04X0mm5GvN4woua6yqI'));
        $this->salt = Salt::by('8872879119a342e522c213e265f464b343a2d8156ab506936e7f3a80cc4f584c');
        $this->passwordVectorMock->method('getSalt')
            ->willReturn($this->salt);

        $this->payload = [
            'username' => 'someUserName',
            'firstName' => 'someFirstName',
            'lastName' => 'someLastName',
            'password' => 'someLoooongPassword',
        ];
        $payloadMock = $this->createMock(Payload::class);
        $payloadMock->method('asArray')
            ->willReturn($this->payload);

        $this->user = RegisteredUser::createByPayload($payloadMock, $this->passwordVectorMock);
    }
}
