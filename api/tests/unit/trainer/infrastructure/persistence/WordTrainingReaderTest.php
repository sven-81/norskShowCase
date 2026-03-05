<?php

declare(strict_types=1);

namespace norsk\api\trainer\infrastructure\persistence;

use norsk\api\infrastructure\persistence\DbConnection;
use norsk\api\infrastructure\persistence\Parameters;
use norsk\api\infrastructure\persistence\SqlResult;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\trainer\domain\exceptions\NoRecordInDatabaseException;
use norsk\api\trainer\infrastructure\persistence\queries\words\GetAllWordsForUserSql;
use norsk\api\user\domain\valueObjects\UserName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(WordTrainingReader::class)]
class WordTrainingReaderTest extends TestCase
{
    private DbConnection|MockObject $dbConnection;

    private UserName $userName;

    private GetAllWordsForUserSql $sql;

    private Parameters $params;


    protected function setUp(): void
    {
        $this->dbConnection = $this->createMock(DbConnection::class);
        $this->userName = UserName::by('someUser');
        $this->sql = GetAllWordsForUserSql::create();
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
                            'german' => 'norwegisch',
                            'norsk' => 'norsk',
                            'successCounter' => 2,
                        ],
                    ]
                )
            );

        $reader = new WordTrainingReader($this->dbConnection);
        $reader->getAllWordsFor($this->userName);
    }


    public function testThrowsExceptionIfNoWordsAreInDatabaseWhileTryingToGetAllWordsForUser(): void
    {
        $this->expectExceptionObject(
            new NoRecordInDatabaseException(
                'No records found in database for: words',
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

        $reader = new WordTrainingReader($this->dbConnection);
        $reader->getAllWordsFor($this->userName);
    }
}
