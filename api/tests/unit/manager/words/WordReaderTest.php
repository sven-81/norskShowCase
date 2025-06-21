<?php

declare(strict_types=1);

namespace norsk\api\manager\words;

use norsk\api\app\persistence\DbConnection;
use norsk\api\app\persistence\Parameters;
use norsk\api\app\persistence\SqlResult;
use norsk\api\app\request\Payload;
use norsk\api\app\response\ResponseCode;
use norsk\api\manager\exceptions\GermanRecordAlreadyInDatabaseException;
use norsk\api\manager\exceptions\RecordAlreadyInDatabaseException;
use norsk\api\manager\Identifier;
use norsk\api\manager\queries\LookForExistingGermanAndNorskVocabularyTupleSql;
use norsk\api\manager\queries\LookForExistingGermanVocabularySql;
use norsk\api\manager\words\queries\GetAllWordsSql;
use norsk\api\shared\Id;
use norsk\api\shared\Vocabularies;
use norsk\api\shared\VocabularyType;
use norsk\api\tests\provider\WordProvider;
use norsk\api\trainer\exceptions\NoRecordInDatabaseException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(WordReader::class)]
class WordReaderTest extends TestCase
{
    private MockObject|DbConnection $dbConnector;


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


    public function testCanEnsureWordsAreNotAlreadyPersisted(): void
    {
        $expectedArray = WordProvider::managedWordArchipelagoAsArray();

        $payload = $this->createMock(Payload::class);
        $payload->expects($this->once())
            ->method('asArray')
            ->willReturn($expectedArray);

        $germanResult = SqlResult::resultFromArray([]);
        $tupleResult = SqlResult::resultFromArray([]);

        $matcher = $this->exactly(2);
        $this->dbConnector
            ->expects($matcher)
            ->method('getResult')
            ->willReturnCallback(
                function (...$args) use ($matcher): void {
                    $expected1 = $this->assertLookingForGermanOnly();
                    $expected2 = $this->assertLookingForGermanAndNorsk();

                    if ($matcher->numberOfInvocations() === 1) {
                        self::assertEquals($expected1, $args);
                    }
                    if ($matcher->numberOfInvocations() === 2) {
                        self::assertEquals($expected2, $args);
                    }
                }
            )
            ->willReturn($germanResult, $tupleResult);

        $wordReader = new WordReader($this->dbConnector);
        $wordReader->ensureWordsAreNotAlreadyPersisted(Id::by(3), $payload);
    }


    private function assertLookingForGermanOnly(): array
    {
        $lookForExistingGermanWordSql = LookForExistingGermanVocabularySql::create(
            VocabularyType::word,
            Id::by(1)
        );
        $parameters = Parameters::init();
        $parameters->addString('Schärenküste');

        return [
            $lookForExistingGermanWordSql,
            $parameters,
        ];
    }


    private function assertLookingForGermanAndNorsk(): array
    {
        $lookForExistingGermanAndNorskWordTupleSql = LookForExistingGermanAndNorskVocabularyTupleSql::create(
            VocabularyType::word,
            null
        );
        $parameters = Parameters::init();
        $parameters->addString('Schärenküste');

        return [
            $lookForExistingGermanAndNorskWordTupleSql,
            $parameters,
        ];
    }


    public function testThrowsExceptionIfWordTupleIsNotUnique(): void
    {
        $id = Id::by(3);
        $identifier = Identifier::fromId($id);
        $this->expectExceptionObject(new RecordAlreadyInDatabaseException($identifier, VocabularyType::word));

        $expectedArray = WordProvider::managedWordArchipelagoAsArray();

        $payload = $this->createMock(Payload::class);
        $payload->expects($this->once())
            ->method('asArray')
            ->willReturn($expectedArray);

        $germanResult = SqlResult::resultFromArray([$expectedArray]);
        $tupleResult = SqlResult::resultFromArray([$expectedArray]);

        $matcher = $this->exactly(2);
        $this->dbConnector
            ->expects($matcher)
            ->method('getResult')
            ->willReturnCallback(
                function (...$args) use ($matcher): void {
                    $expected1 = $this->assertLookingForGermanOnly();
                    $expected2 = $this->assertLookingForGermanAndNorsk();

                    if ($matcher->numberOfInvocations() === 1) {
                        self::assertEquals($expected1, $args);
                    }
                    if ($matcher->numberOfInvocations() === 2) {
                        self::assertEquals($expected2, $args);
                    }
                }
            )
            ->willReturn($germanResult, $tupleResult);

        $wordReader = new WordReader($this->dbConnector);
        $wordReader->ensureWordsAreNotAlreadyPersisted($id, $payload);
    }


    public function testThrowsExceptionIfGermanIsUnique(): void
    {
        $expectedArray = WordProvider::managedWordArchipelagoAsArray();

        $payloadMock = $this->createMock(Payload::class);
        $payloadMock->expects($this->once())
            ->method('asArray')
            ->willReturn($expectedArray);

        $identifier = Identifier::fromPayload($payloadMock);
        $this->expectExceptionObject(new GermanRecordAlreadyInDatabaseException($identifier, VocabularyType::word));

        $germanResult = SqlResult::resultFromArray([$expectedArray]);
        $tupleResult = SqlResult::resultFromArray([]);

        $matcher = $this->exactly(2);
        $this->dbConnector
            ->expects($matcher)
            ->method('getResult')
            ->willReturnCallback(
                function (...$args) use ($matcher): void {
                    $expected1 = $this->assertLookingForGermanOnly();
                    $expected2 = $this->assertLookingForGermanAndNorsk();

                    if ($matcher->numberOfInvocations() === 1) {
                        self::assertEquals($expected1, $args);
                    }
                    if ($matcher->numberOfInvocations() === 2) {
                        self::assertEquals($expected2, $args);
                    }
                }
            )
            ->willReturn($germanResult, $tupleResult);

        $wordReader = new WordReader($this->dbConnector);
        $wordReader->ensureWordsAreNotAlreadyPersisted(null, $payloadMock);
    }


    protected function setUp(): void
    {
        $this->dbConnector = $this->createMock(DbConnection::class);
    }
}
