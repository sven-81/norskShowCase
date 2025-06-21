<?php

declare(strict_types=1);

namespace norsk\api\trainer\verbs;

use norsk\api\app\persistence\DbConnection;
use norsk\api\app\persistence\Parameters;
use norsk\api\app\persistence\SqlResult;
use norsk\api\app\response\ResponseCode;
use norsk\api\trainer\exceptions\NoRecordInDatabaseException;
use norsk\api\trainer\verbs\queries\GetAllVerbsForUserSql;
use norsk\api\user\UserName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(VerbReader::class)]
class VerbReaderTest extends TestCase
{
    private DbConnection|MockObject $dbConnection;

    private UserName $userName;

    private GetAllVerbsForUserSql $sql;

    private Parameters $params;


    public function testCanGetAllWordsForUser(): void
    {
        $this->dbConnection->expects($this->once())
            ->method('getResult')
            ->with(
                $this->sql,
                $this->params
            )
            ->willReturn(
                SqlResult::resultFromArray(
                    [
                        [
                            'id' => 1,
                            'german' => 'essen',
                            'norsk' => 'spise',
                            'norsk_present' => 'spiser',
                            'norsk_past' => 'spiste',
                            'norsk_past_perfekt' => 'spiste',
                            'successCounter' => 2,
                        ],
                    ]
                )
            );

        $reader = new VerbReader($this->dbConnection);
        $reader->getAllVerbsFor($this->userName);
    }


    public function testThrowsExceptionIfNoWordsAreInDatabaseWhileTryingToGetAllWordsForUser(): void
    {
        $this->expectExceptionObject(
            new NoRecordInDatabaseException(
                'No records found in database for: verbs',
                ResponseCode::serverError->value
            )
        );

        $this->dbConnection->expects($this->once())
            ->method('getResult')
            ->with(
                $this->sql,
                $this->params
            )
            ->willReturn(SqlResult::resultFromArray([]));

        $reader = new VerbReader($this->dbConnection);
        $reader->getAllVerbsFor($this->userName);
    }


    protected function setUp(): void
    {
        $this->dbConnection = $this->createMock(DbConnection::class);
        $this->userName = UserName::by('someUser');
        $this->sql = GetAllVerbsForUserSql::create();
        $this->params = Parameters::init();
        $this->params->addString($this->userName->asString());
    }
}
