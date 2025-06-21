<?php

declare(strict_types=1);

namespace norsk\api\trainer\words;

use norsk\api\app\persistence\DbConnection;
use norsk\api\app\persistence\Parameters;
use norsk\api\app\persistence\SqlResult;
use norsk\api\app\response\ResponseCode;
use norsk\api\trainer\exceptions\NoRecordInDatabaseException;
use norsk\api\trainer\words\queries\GetAllWordsForUserSql;
use norsk\api\user\UserName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(WordReader::class)]
class WordReaderTest extends TestCase
{
    private DbConnection|MockObject $dbConnection;

    private UserName $userName;

    private GetAllWordsForUserSql $sql;

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
                            'german' => 'norwegisch',
                            'norsk' => 'norsk',
                            'successCounter' => 2,
                        ],
                    ]
                )
            );

        $reader = new WordReader($this->dbConnection);
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

        $reader = new WordReader($this->dbConnection);
        $reader->getAllWordsFor($this->userName);
    }


    protected function setUp(): void
    {
        $this->dbConnection = $this->createMock(DbConnection::class);
        $this->userName = UserName::by('someUser');
        $this->sql = GetAllWordsForUserSql::create();
        $this->params = Parameters::init();
        $this->params->addString($this->userName->asString());
    }
}
