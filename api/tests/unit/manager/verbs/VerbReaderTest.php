<?php

declare(strict_types=1);

namespace norsk\api\manager\verbs;

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
use norsk\api\manager\verbs\queries\GetAllVerbsSql;
use norsk\api\shared\Id;
use norsk\api\shared\Vocabularies;
use norsk\api\shared\VocabularyType;
use norsk\api\tests\provider\VerbProvider;
use norsk\api\trainer\exceptions\NoRecordInDatabaseException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(VerbReader::class)]
class VerbReaderTest extends TestCase
{
    private MockObject|DbConnection $dbConnector;


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


    public function testCanEnsureVerbsAreNotAlreadyPersisted(): void
    {
        $expectedArray = VerbProvider::managedVerbToGoAsArray();

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

        $verbReader = new VerbReader($this->dbConnector);
        $verbReader->ensureVerbsAreNotAlreadyPersisted(Id::by(1), $payload);
    }


    private function assertLookingForGermanOnly(): array
    {
        $lookForExistingGermanAndNorskVerbTupleSql = LookForExistingGermanAndNorskVocabularyTupleSql::create(
            VocabularyType::verb,
            null
        );
        $parameters = Parameters::init();
        $parameters->addString('norwegisch');

        return [
            $lookForExistingGermanAndNorskVerbTupleSql,
            $parameters,
        ];
    }


    private function assertLookingForGermanAndNorsk(): array
    {
        $lookForExistingGermanVerbSql = LookForExistingGermanVocabularySql::create(
            VocabularyType::verb,
            Id::by(1)
        );
        $parameters = Parameters::init();
        $parameters->addString('norwegisch');

        return [
            $lookForExistingGermanVerbSql,
            $parameters,
        ];
    }


    public function testThrowsExceptionIfVerbTupleIsNotUnique(): void
    {
        $identifier = Identifier::fromId(Id::by(1));
        $this->expectExceptionObject(new RecordAlreadyInDatabaseException($identifier, VocabularyType::verb));

        $expectedArray = VerbProvider::managedVerbToGoAsArray();

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

        $verbReader = new VerbReader($this->dbConnector);
        $verbReader->ensureVerbsAreNotAlreadyPersisted(Id::by(1), $payload);
    }


    public function testThrowsExceptionIfGermanIsUnique(): void
    {
        $expectedArray = VerbProvider::managedVerbToGoAsArray();

        $payloadMock = $this->createMock(Payload::class);
        $payloadMock->expects($this->once())
            ->method('asArray')
            ->willReturn($expectedArray);

        $identifier = Identifier::fromPayload($payloadMock);
        $this->expectExceptionObject(new GermanRecordAlreadyInDatabaseException($identifier, VocabularyType::verb));

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

        $verbReader = new VerbReader($this->dbConnector);
        $verbReader->ensureVerbsAreNotAlreadyPersisted(null, $payloadMock);
    }


    protected function setUp(): void
    {
        $this->dbConnector = $this->createMock(DbConnection::class);
    }
}
