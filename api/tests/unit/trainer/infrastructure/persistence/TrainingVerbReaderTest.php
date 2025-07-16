<?php

declare(strict_types=1);

namespace norsk\api\trainer\infrastructure\persistence;

use norsk\api\infrastructure\persistence\DbConnection;
use norsk\api\infrastructure\persistence\Parameters;
use norsk\api\infrastructure\persistence\SqlResult;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\trainer\domain\exceptions\NoRecordInDatabaseException;
use norsk\api\trainer\infrastructure\persistence\queries\verbs\GetAllVerbsForUserSql;
use norsk\api\user\domain\valueObjects\UserName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(VerbTrainingReader::class)]
class TrainingVerbReaderTest extends TestCase
{
    private DbConnection|MockObject $dbConnection;

    private UserName $userName;

    private GetAllVerbsForUserSql $sql;

    private Parameters $params;


    protected function setUp(): void
    {
        $this->dbConnection = $this->createMock(DbConnection::class);
        $this->userName = UserName::by('someUser');
        $this->sql = GetAllVerbsForUserSql::create();
        $this->params = Parameters::init();
        $this->params->addString($this->userName->asString());
    }


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

        $reader = new VerbTrainingReader($this->dbConnection);
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

        $reader = new VerbTrainingReader($this->dbConnection);
        $reader->getAllVerbsFor($this->userName);
    }
}
