<?php

declare(strict_types=1);

namespace norsk\api\user;

use norsk\api\app\persistence\DbConnection;
use norsk\api\app\persistence\Parameters;
use norsk\api\app\persistence\SqlResult;
use norsk\api\user\exceptions\CredentialsAreInvalidException;
use norsk\api\user\exceptions\NoActiveManagerException;
use norsk\api\user\queries\ActiveManagerSql;
use norsk\api\user\queries\FindUserDataSql;
use norsk\api\user\queries\FindUserSql;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(UsersReader::class)]
class UsersReaderTest extends TestCase
{
    private DbConnection|MockObject $dbConnection;

    private UsersReader $reader;

    private UserName $userName;

    private InputPassword $inputPassword;

    private Pepper $pepper;

    private FindUserDataSql $findUserDataSql;

    private FindUserSql $findUserSql;

    private ActiveManagerSql $activeManager;

    private Parameters $params;


    public function testCanGetDataForUser(): void
    {
        $result = SqlResult::resultFromArray([
            [
                'username' => 'someUser',
                'firstname' => 'james',
                'lastname' => 'last',
                'password_hash' => '$2y$10$VlMTxYn6lnARKkQHq1oSMefy.ELKdsI8wg9XbS9aP115tlSaL7ALm',
                'salt' => 'c651f9240300f3c3bbcc9482105b04c43a5b1b539e3135a565b8a4feab59b6c9',
                'role' => 'manager',
                'active' => 1,
            ],
        ]);

        $this->dbConnection->expects($this->once())
            ->method('getResult')
            ->with($this->findUserDataSql, $this->params)
            ->willReturn($result);

        $expected = ValidatedUser::createBySqlResultAndPasswordHash($result, $this->inputPassword, $this->pepper);
        self::assertEquals($expected, $this->reader->getDataFor($this->userName, $this->inputPassword, $this->pepper));
    }


    public function testThrowsExceptionIfGettingDataForUserFailsBecauseUserDoesNotExist(): void
    {
        $this->expectExceptionObject(new CredentialsAreInvalidException());

        $result = SqlResult::resultFromArray([]);

        $this->dbConnection->expects($this->once())
            ->method('getResult')
            ->with($this->findUserDataSql, $this->params)
            ->willReturn($result);

        $this->reader->getDataFor($this->userName, $this->inputPassword, $this->pepper);
    }


    public function testCanCheckIfUserExists(): void
    {
        $result = SqlResult::resultFromArray([
            [
                'username' => 'someUser',
            ],
        ]);

        $this->dbConnection->expects($this->once())
            ->method('getResult')
            ->with($this->findUserSql, $this->params)
            ->willReturn($result);

        $this->reader->checkIfUserExists($this->userName);
    }


    public function testThrowsExceptionIfUserDoesNotExist(): void
    {
        $this->expectExceptionObject(new CredentialsAreInvalidException());

        $result = SqlResult::resultFromArray([]);

        $this->dbConnection->expects($this->once())
            ->method('getResult')
            ->with($this->findUserSql, $this->params)
            ->willReturn($result);

        $this->reader->checkIfUserExists($this->userName);
    }


    public function testCanCheckIfUserIsActiveManager(): void
    {
        $result = SqlResult::resultFromArray([
            [
                'username' => 'someUser',
            ],
        ]);

        $this->dbConnection->expects($this->once())
            ->method('getResult')
            ->with($this->activeManager, $this->params)
            ->willReturn($result);

        $this->reader->isActiveManager($this->userName);
    }


    public function testThrowsExceptionIfUserIsNoActiveManager(): void
    {
        $this->expectExceptionObject(new NoActiveManagerException('Current user is no active manager'));

        $result = SqlResult::resultFromArray([]);

        $this->dbConnection->expects($this->once())
            ->method('getResult')
            ->with($this->activeManager, $this->params)
            ->willReturn($result);

        $this->reader->isActiveManager($this->userName);
    }


    protected function setUp(): void
    {
        $this->dbConnection = $this->createMock(DbConnection::class);
        $this->reader = new UsersReader($this->dbConnection);

        $this->userName = UserName::by('someUser');
        $this->inputPassword = InputPassword::by('someSecretlySecret');
        $this->pepper = Pepper::by('iwwBYerIjfYhu04X0mm5GvN4woua6yqI');

        $this->findUserDataSql = FindUserDataSql::create();
        $this->findUserSql = FindUserSql::create();
        $this->activeManager = ActiveManagerSql::create();
        $this->params = Parameters::init();
        $this->params->addString('someUser');
    }
}
