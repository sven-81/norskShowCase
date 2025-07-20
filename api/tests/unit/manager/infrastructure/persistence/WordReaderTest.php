<?php

declare(strict_types=1);

namespace norsk\api\manager\infrastructure\persistence;

use norsk\api\infrastructure\persistence\DbConnection;
use norsk\api\infrastructure\persistence\Parameters;
use norsk\api\infrastructure\persistence\SqlResult;
use norsk\api\manager\infrastructure\persistence\queries\words\GetAllWordsSql;
use norsk\api\shared\domain\Vocabularies;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\tests\provider\WordProvider;
use norsk\api\trainer\domain\exceptions\NoRecordInDatabaseException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(WordReader::class)]
class WordReaderTest extends TestCase
{
    private MockObject|DbConnection $dbConnector;


    protected function setUp(): void
    {
        $this->dbConnector = $this->createMock(DbConnection::class);
    }


    public function testCanGetAllWords(): void
    {
        $word = WordProvider::managedWordArchipelago();
        $expectedWords = Vocabularies::create();
        $expectedWords->add($word);

        $array = WordProvider::managedWordArchipelagoAsArray();
        $result = SqlResult::resultFromArray([$array,]);

        $getAllWordsSql = GetAllWordsSql::create();
        $this->dbConnector->expects($this->once())
            ->method('getResult')
            ->with(
                $getAllWordsSql,
                Parameters::init()
            )
            ->willReturn($result);

        $wordReader = new WordReader($this->dbConnector);
        self::assertEquals($expectedWords, $wordReader->getAllWords());
    }


    public function testThrowsExceptionIfNoRecordsAreFoundWhileTryingToGetAllWords(): void
    {
        $this->expectExceptionObject(
            new NoRecordInDatabaseException(
                'No records found in database for: words',
                ResponseCode::serverError->value
            )
        );

        $result = SqlResult::resultFromArray([]);
        $getAllWordsSql = GetAllWordsSql::create();
        $this->dbConnector->expects($this->once())
            ->method('getResult')
            ->with(
                $getAllWordsSql,
                Parameters::init()
            )
            ->willReturn($result);

        $wordReader = new WordReader($this->dbConnector);
        $wordReader->getAllWords();
    }

}
