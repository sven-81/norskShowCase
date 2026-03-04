<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\persistence;

use LogicException;
use mysqli_result;
use norsk\api\infrastructure\config\DbConfig;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(DbConnection::class)]
class DbConnectionTest extends TestCase
{
    private const int PORT = 3306;

    private MysqliWrapper|MockObject $mysqliMock;

    private string $query;


    protected function setUp(): void
    {
        $this->mysqliMock = $this->createMock(MysqliWrapper::class);
        $this->query = 'SELECT * FROM test';
    }


    public function testCanConnectToDatabase(): void
    {
        $dbConfigMock = $this->createMock(DbConfig::class);
        $dbConfigMock->expects($this->once())
            ->method('host')
            ->willReturn(Host::fromString('host'));
        $this->configureDbConfigMock($dbConfigMock);

        $this->mysqliMock->expects($this->once())
            ->method('connect')
            ->with(
                'host',
                'user',
                'password',
                'database',
                self::PORT
            )
            ->willReturn(true);

        $this->mysqliMock->expects($this->once())
            ->method('set_charset')
            ->with('utf8');

        $dbConnection = new DbConnection($this->mysqliMock, $dbConfigMock);
        $dbConnection->createConnection();
    }


    private function configureDbConfigMock(MockObject $dbConfigMock): void
    {
        $dbConfigMock->expects($this->once())
            ->method('user')
            ->willReturn(User::fromString('user'));
        $dbConfigMock->expects($this->once())
            ->method('password')
            ->willReturn(Password::fromString('password'));
        $dbConfigMock->expects($this->once())
            ->method('database')
            ->willReturn(DatabaseName::fromString('database'));
        $dbConfigMock->expects($this->once())
            ->method('port')
            ->willReturn(Port::fromInt(3306));
    }


    public function testThrowsExceptionIfDatabaseRefusesToConnect(): void
    {
        $this->expectExceptionObject(
            new RuntimeException(
                'Could not connect to server: host. Because: mööp',
                ResponseCode::serverError->value
            )
        );

        $dbConfigMock = $this->createMock(DbConfig::class);
        $dbConfigMock->expects($this->exactly(2))
            ->method('host')
            ->willReturn(Host::fromString('host'));

        $this->mysqliMock->expects($this->once())
            ->method('connect')
            ->willThrowException(
                new RuntimeException('mööp')
            );
        $this->mysqliMock->expects($this->never())
            ->method('set_charset');

        $dbConnection = new DbConnection($this->mysqliMock, $dbConfigMock);
        $dbConnection->createConnection();
    }


    public function testCanGetResult(): void
    {
        $sqlMock = $this->createMock(GenericSqlStatement::class);
        $sqlMock->expects($this->once())
            ->method('asString')
            ->willReturn($this->query);

        $resultMock = $this->createMock(mysqli_result::class);
        $resultMock->expects($this->once())
            ->method('fetch_all')
            ->willReturn([['foo', 'bar']]);

        $this->mysqliMock->expects($this->once())
            ->method('connect')
            ->willReturn(true);
        $this->mysqliMock->expects($this->once())
            ->method('execute_query')
            ->with($this->query, [])
            ->willReturn($resultMock);

        $paramsMock = $this->createMock(Parameters::class);
        $paramsMock->expects($this->once())
            ->method('asArray')
            ->willReturn([]);

        $dbConnection = new DbConnection($this->mysqliMock, $this->createStub(DbConfig::class));
        self::assertEquals(
            SqlResult::resultFromArray([['foo', 'bar']]),
            $dbConnection->getResult($sqlMock, $paramsMock)
        );
    }


    public function testThrowsExceptionIfGetResultsWasNotImplementedForTheRightQuery(): void
    {
        $this->expectExceptionObject(
            new LogicException('getResults is supposed to be used for SELECT, SHOW, DESCRIBE or EXPLAIN')
        );

        $sqlMock = $this->createMock(GenericSqlStatement::class);
        $sqlMock->expects($this->once())
            ->method('asString')
            ->willReturn($this->query);

        $this->mysqliMock->expects($this->once())
            ->method('connect')
            ->willReturn(true);
        $this->mysqliMock->expects($this->once())
            ->method('execute_query')
            ->with($this->query, [])
            ->willReturn(true);

        $paramsMock = $this->createMock(Parameters::class);
        $paramsMock->expects($this->once())
            ->method('asArray')
            ->willReturn([]);

        $dbConnection = new DbConnection($this->mysqliMock, $this->createStub(DbConfig::class));
        self::assertEquals(
            SqlResult::resultFromArray([['foo', 'bar']]),
            $dbConnection->getResult($sqlMock, $paramsMock)
        );
    }


    public function testThrowsExceptionIfCannotGetResult(): void
    {
        $this->expectExceptionObject(
            new RuntimeException('Could not execute query: ' . $this->query)
        );

        $sqlMock = $this->createMock(GenericSqlStatement::class);
        $sqlMock->expects($this->once())
            ->method('asString')
            ->willReturn($this->query);

        $this->mysqliMock->expects($this->once())
            ->method('connect')
            ->willReturn(true);
        $this->mysqliMock->expects($this->once())
            ->method('execute_query')
            ->with($this->query, [])
            ->willReturn(false);

        $paramsMock = $this->createMock(Parameters::class);
        $paramsMock->expects($this->once())
            ->method('asArray')
            ->willReturn([]);

        $dbConnection = new DbConnection($this->mysqliMock, $this->createStub(DbConfig::class));
        $dbConnection->getResult($sqlMock, $paramsMock);
    }


    public function testCanExecute(): void
    {
        $sqlMock = $this->createMock(GenericSqlStatement::class);
        $sqlMock->expects($this->once())
            ->method('asString')
            ->willReturn($this->query);

        $this->mysqliMock->expects($this->once())
            ->method('connect')
            ->willReturn(true);
        $this->mysqliMock->expects($this->once())
            ->method('execute_query')
            ->with($this->query, [])
            ->willReturn(true);
        $this->mysqliMock->expects($this->once())
            ->method('affectedRows')
            ->willReturn(1);

        $paramsMock = $this->createMock(Parameters::class);
        $paramsMock->expects($this->once())
            ->method('asArray')
            ->willReturn([]);

        $dbConnection = new DbConnection($this->mysqliMock, $this->createStub(DbConfig::class));
        self::assertEquals(
            AffectedRows::fromInt(1),
            $dbConnection->execute($sqlMock, $paramsMock)
        );
    }
}
