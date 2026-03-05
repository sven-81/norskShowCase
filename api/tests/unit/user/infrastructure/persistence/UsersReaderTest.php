<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\persistence;

use InvalidArgumentException;
use norsk\api\infrastructure\persistence\DbConnection;
use norsk\api\infrastructure\persistence\Parameters;
use norsk\api\infrastructure\persistence\SqlResult;
use norsk\api\user\domain\exceptions\CredentialsAreInvalidException;
use norsk\api\user\domain\exceptions\NoActiveManagerException;
use norsk\api\user\domain\model\Role;
use norsk\api\user\domain\model\UserData;
use norsk\api\user\domain\valueObjects\UserName;
use norsk\api\user\infrastructure\persistence\queries\ActiveManagerSql;
use norsk\api\user\infrastructure\persistence\queries\FindUserDataSql;
use norsk\api\user\infrastructure\persistence\queries\FindUserSql;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(UsersReader::class)]
class UsersReaderTest extends TestCase
{
    private DbConnection|MockObject $dbConnection;

    private UsersReader $reader;

    private UserName $userName;

    private FindUserDataSql $findUserDataSql;

    private FindUserSql $findUserSql;

    private ActiveManagerSql $activeManager;

    private Parameters $params;

    private array $validUserRecord;


    protected function setUp(): void
    {
        $this->dbConnection = $this->createMock(DbConnection::class);
        $this->reader = new UsersReader($this->dbConnection);

        $this->userName = UserName::by('someUser');

        $this->findUserDataSql = FindUserDataSql::create();
        $this->findUserSql = FindUserSql::create();
        $this->activeManager = ActiveManagerSql::create();

        $this->params = Parameters::init();
        $this->params->addString('someUser');

        $this->validUserRecord = [
            'username' => 'someUser',
            'firstname' => 'james',
            'lastname' => 'last',
            'password_hash' => '$2y$10$VlMTxYn6lnARKkQHq1oSMefy.ELKdsI8wg9XbS9aP115tlSaL7ALm',
            'salt' => 'c651f9240300f3c3bbcc9482105b04c43a5b1b539e3135a565b8a4feab59b6c9',
            'role' => 'manager',
            'active' => 1,
        ];
    }


    public function testFindByUserNameReturnsUserData(): void
    {
        $result = SqlResult::resultFromArray([$this->validUserRecord]);

        $this->dbConnection->expects($this->once())
            ->method('getResult')
            ->with($this->findUserDataSql, $this->params)
            ->willReturn($result);

        $userData = $this->reader->findByUserName($this->userName);

        self::assertInstanceOf(UserData::class, $userData);
        self::assertSame('someUser', $userData->userName->asString());
        self::assertSame('james', $userData->firstName->asString());
        self::assertSame('last', $userData->lastName->asString());
        self::assertSame(Role::MANAGER, $userData->role);
        self::assertTrue($userData->isActive);
    }


    public static function getMissingField(): array
    {
        return [
            'username missing' => ['username'],
            'firstname missing' => ['firstname'],
            'lastname missing' => ['lastname'],
            'password_hash missing' => ['password_hash'],
            'salt missing' => ['salt'],
            'role missing' => ['role'],
            'active missing' => ['active'],
        ];
    }


    #[DataProvider('getMissingField')]
    public function testThrowsExceptionIfRequiredFieldIsMissingInUserRecord(string $missingField): void
    {
        $this->expectExceptionObject(
            new InvalidArgumentException('Missing field in user record: ' . $missingField)
        );

        $incompleteRecord = $this->validUserRecord;
        unset($incompleteRecord[$missingField]);

        $result = SqlResult::resultFromArray([$incompleteRecord]);

        $this->dbConnection->expects($this->once())
            ->method('getResult')
            ->with($this->findUserDataSql, $this->params)
            ->willReturn($result);

        $this->reader->findByUserName($this->userName);
    }


    public function testFindByUserNameThrowsExceptionIfUserDoesNotExist(): void
    {
        $this->expectExceptionObject(new CredentialsAreInvalidException());

        $result = SqlResult::resultFromArray([]);

        $this->dbConnection->expects($this->once())
            ->method('getResult')
            ->with($this->findUserDataSql, $this->params)
            ->willReturn($result);

        $this->reader->findByUserName($this->userName);
    }


    public function testCanCheckIfUserExists(): void
    {
        $result = SqlResult::resultFromArray([['username' => 'someUser']]);

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
        $result = SqlResult::resultFromArray([['username' => 'someUser']]);

        $this->dbConnection->expects($this->once())
            ->method('getResult')
            ->with($this->activeManager, $this->params)
            ->willReturn($result);

        $this->reader->isActiveManager($this->userName);
    }


    public function testThrowsExceptionIfUserIsNoActiveManager(): void
    {
        $this->expectExceptionObject(
            new NoActiveManagerException('Unauthorized: Current user is no active manager')
        );

        $result = SqlResult::resultFromArray([]);

        $this->dbConnection->expects($this->once())
            ->method('getResult')
            ->with($this->activeManager, $this->params)
            ->willReturn($result);

        $this->reader->isActiveManager($this->userName);
    }
}
