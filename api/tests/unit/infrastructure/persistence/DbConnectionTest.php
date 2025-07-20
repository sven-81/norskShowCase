<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\persistence;

use LogicException;
use mysqli_result;
use norsk\api\infrastructure\config\DbConfig;
use norsk\api\infrastructure\persistence\AffectedRows;
use norsk\api\infrastructure\persistence\DatabaseName;
use norsk\api\infrastructure\persistence\DbConnection;
use norsk\api\infrastructure\persistence\GenericSqlStatement;
use norsk\api\infrastructure\persistence\Host;
use norsk\api\infrastructure\persistence\MysqliWrapper;
use norsk\api\infrastructure\persistence\Parameters;
use norsk\api\infrastructure\persistence\Password;
use norsk\api\infrastructure\persistence\Port;
use norsk\api\infrastructure\persistence\SqlResult;
use norsk\api\infrastructure\persistence\User;
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

    private MockObject|DbConfig $dbConfigMock;

    private Parameters|MockObject $paramsMock;

    private string $query;

    private GenericSqlStatement|MockObject $sqlMock;


    protected function setUp(): void
    {
        $this->mysqliMock = $this->createMock(MysqliWrapper::class);
        $this->dbConfigMock = $this->createMock(DbConfig::class);

        $this->paramsMock = $this->createMock(Parameters::class);
        $this->query = 'SELECT * FROM test';

        $this->sqlMock = $this->createMock(GenericSqlStatement::class);
    }


    public function testCanConnectToDatabase(): void
    {
        $this->dbConfigMock->expects($this->once())
            ->method('host')
            ->willReturn(Host::fromString('host'));
        $this->dbConfigMock();

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

        $dbConnection = new DbConnection($this->mysqliMock, $this->dbConfigMock);
        $dbConnection->createConnection();
    }


    private function dbConfigMock(): void
    {
        $this->dbConfigMock->expects($this->once())
            ->method('user')
            ->willReturn(User::fromString('user'));
        $this->dbConfigMock->expects($this->once())
            ->method('password')
            ->willReturn(Password::fromString('password'));
        $this->dbConfigMock->expects($this->once())
            ->method('database')
            ->willReturn(DatabaseName::fromString('database'));
        $this->dbConfigMock->expects($this->once())
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

        $this->dbConfigMock->expects($this->exactly(2))
            ->method('host')
            ->willReturn(Host::fromString('host'));

        $this->mysqliMock->expects($this->once())
            ->method('connect')
            ->willThrowException(
                new RuntimeException('mööp')
            );
        $this->mysqliMock->expects($this->never())
            ->method('set_charset');

        $dbConnection = new DbConnection($this->mysqliMock, $this->dbConfigMock);
        $dbConnection->createConnection();
    }


    public function testCanGetResult(): void
    {
        $this->sqlMock->expects($this->once())
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

        $this->paramsMock->expects($this->once())
            ->method('asArray')
            ->willReturn([]);

        $dbConnection = new DbConnection($this->mysqliMock, $this->dbConfigMock);
        self::assertEquals(
            SqlResult::resultFromArray([['foo', 'bar']]),
            $dbConnection->getResult($this->sqlMock, $this->paramsMock)
        );
    }


    public function testThrowsExceptionIfGetResultsWasNotImplementedForTheRightQuery(): void
    {
        $this->expectExceptionObject(
            new LogicException('getResults is supposed to be used for SELECT, SHOW, DESCRIBE or EXPLAIN')
        );

        $this->sqlMock->expects($this->once())
            ->method('asString')
            ->willReturn($this->query);

        $this->mysqliMock->expects($this->once())
            ->method('connect')
            ->willReturn(true);
        $this->mysqliMock->expects($this->once())
            ->method('execute_query')
            ->with($this->query, [])
            ->willReturn(true);

        $this->paramsMock->expects($this->once())
            ->method('asArray')
            ->willReturn([]);

        $dbConnection = new DbConnection($this->mysqliMock, $this->dbConfigMock);
        self::assertEquals(
            SqlResult::resultFromArray([['foo', 'bar']]),
            $dbConnection->getResult($this->sqlMock, $this->paramsMock)
        );
    }


    public function testThrowsExceptionIfCannotGetResult(): void
    {
        $this->expectExceptionObject(
            new RuntimeException('Could not execute query: ' . $this->query)
        );

        $this->sqlMock->method('asString')
            ->willReturn($this->query);

        $this->mysqliMock->expects($this->once())
            ->method('connect')
            ->willReturn(true);
        $this->mysqliMock->expects($this->once())
            ->method('execute_query')
            ->with($this->query, [])
            ->willReturn(false);

        $dbConnection = new DbConnection($this->mysqliMock, $this->dbConfigMock);
        $dbConnection->getResult($this->sqlMock, $this->paramsMock);
    }


    public function testCanExecute(): void
    {
        $this->sqlMock->expects($this->once())
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

        $this->paramsMock->expects($this->once())
            ->method('asArray')
            ->willReturn([]);

        $dbConnection = new DbConnection($this->mysqliMock, $this->dbConfigMock);
        self::assertEquals(
            AffectedRows::fromInt(1),
            $dbConnection->execute($this->sqlMock, $this->paramsMock)
        );
    }
}
