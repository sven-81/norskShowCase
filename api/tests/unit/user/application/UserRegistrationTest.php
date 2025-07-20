<?php

declare(strict_types=1);

namespace norsk\api\user\application;

use norsk\api\shared\infrastructure\http\request\Payload;
use norsk\api\user\application\useCases\RegisterUser;
use norsk\api\user\domain\port\UserWritingRepository;
use norsk\api\user\domain\valueObjects\FirstName;
use norsk\api\user\domain\valueObjects\InputPassword;
use norsk\api\user\domain\valueObjects\LastName;
use norsk\api\user\domain\valueObjects\PasswordVector;
use norsk\api\user\domain\valueObjects\UserName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserRegistration::class)]
class UserRegistrationTest extends TestCase
{

    public function testCanHandleCommand(): void
    {
        $userName = UserName::by('someUserName');
        $firstName = FirstName::by('someFirstName');
        $lastName = LastName::by('someLastName');
        $inputPassword = InputPassword::by('someLoooongPassword');

        $payload = [
            'username' => 'someUserName',
            'firstName' => 'someFirstName',
            'lastName' => 'someLastName',
            'password' => 'someLoooongPassword',
        ];

        $payloadMock = $this->createMock(Payload::class);
        $payloadMock->method('asArray')
            ->willReturn($payload);

        $passwordVectorMock = $this->createMock(PasswordVector::class);

        $commandMock = $this->createMock(RegisterUser::class);
        $commandMock->expects($this->once())
            ->method('getUserName')
            ->willReturn($userName);
        $commandMock->expects($this->once())
            ->method('getFirstName')
            ->willReturn($firstName);
        $commandMock->expects($this->once())
            ->method('getLastName')
            ->willReturn($lastName);
        $commandMock->expects($this->once())
            ->method('getInputPassword')
            ->willReturn($inputPassword);

        $repositoryMock = $this->createMock(UserWritingRepository::class);
        $repositoryMock->expects($this->once())
            ->method('add');

        $handler = new UserRegistration($repositoryMock, $passwordVectorMock);
        $handler->handle($commandMock);
    }
}
