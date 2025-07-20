<?php

declare(strict_types=1);

namespace norsk\api\manager\infrastructure\persistence;

use norsk\api\infrastructure\persistence\DbConnection;
use norsk\api\infrastructure\persistence\Parameters;
use norsk\api\infrastructure\persistence\SqlResult;
use norsk\api\manager\domain\exceptions\GermanRecordAlreadyInDatabaseException;
use norsk\api\manager\domain\exceptions\RecordAlreadyInDatabaseException;
use norsk\api\manager\domain\Identifier;
use norsk\api\manager\infrastructure\persistence\queries\LookForExistingGermanAndNorskVocabularyTupleSql;
use norsk\api\manager\infrastructure\persistence\queries\LookForExistingGermanVocabularySql;
use norsk\api\shared\domain\German;
use norsk\api\shared\domain\Id;
use norsk\api\shared\domain\Norsk;
use norsk\api\shared\domain\VocabularyType;
use norsk\api\tests\provider\WordProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(SqlUniquenessPolicy::class)]
class SqlUniquenessPolicyTest extends TestCase
{

    private DbConnection|MockObject $dbConnectionMock;

    private Id $id;

    private German $german;

    private Norsk $norsk;


    protected function setUp(): void
    {
        $this->dbConnectionMock = $this->createMock(DbConnection::class);
        $this->id = Id::by(1);
        $this->german = German::of('Wort');
        $this->norsk = Norsk::of('ord');
    }


    public static function getIdentifier(): array
    {
        return [
            'IdentifierAsId' => [Id::by(1),],
            'IdentifierAsString' => [null,],
        ];
    }


    public static function getVocabularyType(): array
    {
        return [
            'word with id' => [VocabularyType::word, Id::by(1)],
            'verb without id' => [VocabularyType::verb, null],
        ];
    }


    #[DataProvider('getVocabularyType')]
    public function testCanEnsureVocabsAreNotAlreadyPersisted(VocabularyType $type, ?Id $id): void
    {
        $germanResult = SqlResult::resultFromArray([]);
        $tupleResult = SqlResult::resultFromArray([]);
        $matcher = $this->exactly(2);
        $this->dbConnectionMock
            ->expects($matcher)
            ->method('getResult')
            ->willReturnCallback(
                function ($sql, $params) use ($matcher, $type, $id): void {
                    $expected1 = $this->assertLookingForGermanOnly($id, $type);
                    $expected2 = $this->assertLookingForGermanAndNorsk($id, $type);

                    if ($matcher->numberOfInvocations() === 1) {
                        self::assertEquals($expected1, [$sql, $params]);
                    }
                    if ($matcher->numberOfInvocations() === 2) {
                        self::assertEquals($expected2, [$sql, $params]);
                    }
                }
            )
            ->willReturn($germanResult, $tupleResult);

        $policy = new SqlUniquenessPolicy($this->dbConnectionMock, $type);
        $policy->ensureVocabularyIsNotAlreadyPersisted($id, $this->german, $this->norsk);
    }


    public function testThrowsExceptionIfGermanIsUnique(): void
    {
        $identifier = Identifier::fromId(Id::by(1));
        $this->expectExceptionObject(
            new GermanRecordAlreadyInDatabaseException($identifier, VocabularyType::word)
        );

        $expectedArray = WordProvider::managedWordArchipelagoAsArray();

        $germanResult = SqlResult::resultFromArray([$expectedArray]);
        $tupleResult = SqlResult::resultFromArray([]);
        $matcher = $this->exactly(2);
        $this->dbConnectionMock
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

        $policy = new SqlUniquenessPolicy($this->dbConnectionMock, VocabularyType::word);
        $policy->ensureVocabularyIsNotAlreadyPersisted($this->id, $this->german, $this->norsk);
    }


    public function testThrowsExceptionIfWordTupleIsNotUnique(): void
    {
        $identifier = Identifier::fromId(Id::by(1));
        $this->expectExceptionObject(
            new RecordAlreadyInDatabaseException($identifier, VocabularyType::word)
        );

        $expectedArray = WordProvider::managedWordArchipelagoAsArray();

        $germanResult = SqlResult::resultFromArray([$expectedArray]);
        $tupleResult = SqlResult::resultFromArray([$expectedArray]);
        $matcher = $this->exactly(2);
        $this->dbConnectionMock
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

        $policy = new SqlUniquenessPolicy($this->dbConnectionMock, VocabularyType::word);
        $policy->ensureVocabularyIsNotAlreadyPersisted($this->id, $this->german, $this->norsk);
    }


    private function assertLookingForGermanOnly(?Id $id = null, VocabularyType $type = VocabularyType::word): array
    {
        $lookForExistingGermanVocabSql = LookForExistingGermanVocabularySql::create($type, $id);
        $parameters = Parameters::init();
        $parameters->addString('Wort');

        return [
            $lookForExistingGermanVocabSql,
            $parameters,
        ];
    }


    private function assertLookingForGermanAndNorsk(?Id $id = null, VocabularyType $type = VocabularyType::word): array
    {
        $lookForExistingGermanAndNorskVocabTupleSql = LookForExistingGermanAndNorskVocabularyTupleSql::create(
            $type,
            $id
        );
        $parameters = Parameters::init();
        $parameters->addString('Wort');

        return [
            $lookForExistingGermanAndNorskVocabTupleSql,
            $parameters,
        ];
    }
}
