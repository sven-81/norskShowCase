<?php

declare(strict_types=1);

namespace norsk\api\user\application;

use norsk\api\user\application\useCases\LoginUser;
use norsk\api\user\domain\model\ValidatedUser;
use norsk\api\user\domain\port\UserReadingRepository;
use norsk\api\user\domain\valueObjects\InputPassword;
use norsk\api\user\domain\valueObjects\Pepper;
use norsk\api\user\domain\valueObjects\UserName;
use norsk\api\user\infrastructure\identityAccessManagement\jwt\JwtManagement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserLogin::class)]
class UserLoginTest extends TestCase
{

    public function testCanHandleCommand(): void
    {
        $userName = UserName::by('someUser');
        $password = InputPassword::by('shhhhhhhhhhhhhhhhhhhhhhhhhh!!');

        $commandMock = $this->createMock(LoginUser::class);
        $commandMock->expects($this->once())
            ->method('getUserName')
            ->willReturn($userName);
        $commandMock->expects($this->once())
            ->method('getPassword')
            ->willReturn($password);

        $pepper = $this->createMock(Pepper::class);

        $validatedUserMock = $this->createMock(ValidatedUser::class);

        $repositoryMock = $this->createMock(UserReadingRepository::class);
        $repositoryMock->expects($this->once())
            ->method('getDataFor')
            ->with($userName, $password, $pepper)
            ->willReturn($validatedUserMock);

        $jwtManagementMock = $this->createMock(JwtManagement::class);
        $jwtManagementMock->expects($this->once())
            ->method('create')
            ->with($validatedUserMock);

        $handler = new UserLogin($repositoryMock, $pepper, $jwtManagementMock);
        $handler->handle($commandMock);
    }
}
