<?php

declare(strict_types=1);

namespace norsk\api\manager\infrastructure\persistence;

use norsk\api\infrastructure\persistence\DbConnection;
use norsk\api\infrastructure\persistence\Parameters;
use norsk\api\infrastructure\persistence\SqlResult;
use norsk\api\manager\infrastructure\persistence\queries\verbs\GetAllVerbsSql;
use norsk\api\shared\domain\Vocabularies;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\tests\provider\VerbProvider;
use norsk\api\trainer\domain\exceptions\NoRecordInDatabaseException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(VerbReader::class)]
class VerbReaderTest extends TestCase
{
    private MockObject|DbConnection $dbConnector;


    protected function setUp(): void
    {
        $this->dbConnector = $this->createMock(DbConnection::class);
    }


    public function testCanGetAllVerbs(): void
    {
        $verb = VerbProvider::managedVerbToGo();
        $expectedVerbs = Vocabularies::create();
        $expectedVerbs->add($verb);

        $array = VerbProvider::managedVerbToGoFromRecord();
        $result = SqlResult::resultFromArray([$array]);
        $getAllVerbsSql = GetAllVerbsSql::create();
        $this->dbConnector->expects($this->once())
            ->method('getResult')
            ->with(
                $getAllVerbsSql,
                Parameters::init()
            )
            ->willReturn($result);

        $verbReader = new VerbReader($this->dbConnector);
        self::assertEquals($expectedVerbs, $verbReader->getAllVerbs());
    }


    public function testThrowsExceptionIfNoRecordsAreFoundWhileTryingToGetAllVerbs(): void
    {
        $this->expectExceptionObject(
            new NoRecordInDatabaseException(
                'No records found in database for: verbs',
                ResponseCode::serverError->value
            )
        );

        $result = SqlResult::resultFromArray([]);
        $getAllVerbsSql = GetAllVerbsSql::create();
        $this->dbConnector->expects($this->once())
            ->method('getResult')
            ->with(
                $getAllVerbsSql,
                Parameters::init()
            )
            ->willReturn($result);

        $verbReader = new VerbReader($this->dbConnector);
        $verbReader->getAllVerbs();
    }
}
