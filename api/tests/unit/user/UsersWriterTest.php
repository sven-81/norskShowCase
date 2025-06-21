<?php

declare(strict_types=1);

namespace norsk\api\user;

use norsk\api\app\persistence\DbConnection;
use norsk\api\app\persistence\Parameters;
use norsk\api\user\queries\AddUserSql;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UsersWriter::class)]
class UsersWriterTest extends TestCase
{
    public function testCanAddUser(): void
    {
        $userName = UserName::by('someUserName');
        $firstName = FirstName::by('someFirstName');
        $lastName = LastName::by('someLastName');
        $password = PasswordHash::by('$2y$10$QXYN9Xkf2tQ/o/gjYIr0LuJ.HHmaF6FXP7Sddb6jZmNs4udKDi5.K');
        $salt = Salt::by('8872879119a342e522c213e265f464b343a2d8156ab506936e7f3a80cc4f584c');

        $userMock = $this->createMock(RegisteredUser::class);
        $userMock->expects($this->once())
            ->method('getUserName')
            ->willReturn($userName);
        $userMock->expects($this->once())
            ->method('getFirstName')
            ->willReturn($firstName);
        $userMock->expects($this->once())
            ->method('getLastName')
            ->willReturn($lastName);
        $userMock->expects($this->once())
            ->method('getPasswordHash')
            ->willReturn($password);
        $userMock->expects($this->once())
            ->method('getSalt')
            ->willReturn($salt);

        $params = Parameters::init();
        $params->addString($userName->asString());
        $params->addString($firstName->asString());
        $params->addString($lastName->asString());
        $params->addString($password->asHashString());
        $params->addString($salt->asString());

        $dbConnection = $this->createMock(DbConnection::class);
        $dbConnection->expects($this->once())
            ->method('execute')
            ->with(
                AddUserSql::create(),
                $params
            );

        $userWriter = new UsersWriter($dbConnection);
        $userWriter->add($userMock);
    }
}
