<?php

declare(strict_types=1);

namespace norsk\api\user\application;

use norsk\api\user\application\useCases\LoginUser;
use norsk\api\user\domain\model\AuthToken;
use norsk\api\user\domain\model\Role;
use norsk\api\user\domain\model\UserData;
use norsk\api\user\domain\port\UserReadingRepository;
use norsk\api\user\domain\service\JwtService;
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

#[CoversClass(UserLogin::class)]
class UserLoginTest extends TestCase
{
    public function testHandleReturnsLoggedInUserOnValidCredentials(): void
    {
        $rawPassword = 'shhhhhhhhhhhhhhhhhhhhhhhhhh!!';
        $pepperString = str_repeat('p', 32);
        $saltString = str_repeat('s', 32);

        $pepper = Pepper::by($pepperString);
        $salt = Salt::by($saltString);
        $vector = PasswordVector::by($salt, $pepper);
        $password = InputPassword::by($rawPassword);
        $hash = PasswordHash::hashBy($password, $vector);

        // Salt wurde durch hashBy::generate() überschrieben – aktuellen Wert für UserData lesen
        $currentSalt = Salt::by($salt->asString());

        $userName = UserName::by('someUser');

        $userData = UserData::of(
            userName: $userName,
            firstName: FirstName::by('Max'),
            lastName: LastName::by('Muster'),
            passwordHash: $hash,
            salt: $currentSalt,
            role: Role::USER,
            isActive: true,
        );

        $commandStub = $this->createStub(LoginUser::class);
        $commandStub->method('getUserName')->willReturn($userName);
        $commandStub->method('getPassword')->willReturn($password);

        $repositoryMock = $this->createMock(UserReadingRepository::class);
        $repositoryMock->expects($this->once())
            ->method('findByUserName')
            ->with($userName)
            ->willReturn($userData);

        $tokenStub = $this->createStub(AuthToken::class);
        $jwtMock = $this->createMock(JwtService::class);
        $jwtMock->expects($this->once())
            ->method('create')
            ->willReturn($tokenStub);

        $handler = new UserLogin($repositoryMock, $pepper, $jwtMock);
        $result = $handler->handle($commandStub);

        $this->assertSame($userName->asString(), $result->getUserName()->asString());
    }
}
