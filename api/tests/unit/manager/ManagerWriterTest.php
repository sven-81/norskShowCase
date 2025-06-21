<?php

declare(strict_types=1);

namespace norsk\api\manager;

use norsk\api\app\persistence\AffectedRows;
use norsk\api\app\persistence\DbConnection;
use norsk\api\app\persistence\Parameters;
use norsk\api\app\request\Payload;
use norsk\api\manager\exceptions\NoRecordInDatabaseException;
use norsk\api\manager\queries\RemoveVocabularySql;
use norsk\api\manager\verbs\queries\AddingVerbsSql;
use norsk\api\manager\verbs\queries\EditVerbsSql;
use norsk\api\manager\words\queries\AddingWordsSql;
use norsk\api\manager\words\queries\EditWordsSql;
use norsk\api\shared\Id;
use norsk\api\shared\VocabularyType;
use norsk\api\tests\provider\VerbProvider;
use norsk\api\tests\provider\WordProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(ManagerWriter::class)]
class ManagerWriterTest extends TestCase
{
    private DbConnection|MockObject $dbConnection;

    private Id $id;

    private VocabularyType $word;

    private VocabularyType $verb;


    public function testCanAddWord(): void
    {
        $payload = $this->getWordPayload();

        $addingWordsSql = AddingWordsSql::create();
        $params = Parameters::init();
        $params->addString('Schärenküste');
        $params->addString('skjærgård');

        $this->dbConnection->expects($this->once())
            ->method('execute')
            ->with(
                $addingWordsSql,
                $params
            );

        $writer = new ManagerWriter($this->dbConnection);
        $writer->add($payload, $this->word);
    }


    private function getWordPayload(): MockObject|Payload
    {
        $expectedArray = WordProvider::managedWordArchipelagoAsArray();
        $payload = $this->createMock(Payload::class);
        $payload->expects($this->once())
            ->method('asArray')
            ->willReturn($expectedArray);

        return $payload;
    }


    public function testCanAddVerb(): void
    {
        $payload = $this->getVerbPayload();

        $addingVerbsSql = AddingVerbsSql::create();
        $params = Parameters::init();
        $params->addString('gehen');
        $params->addString('gå');
        $params->addString('går');
        $params->addString('gikk');
        $params->addString('har gått');

        $this->dbConnection->expects($this->once())
            ->method('execute')
            ->with(
                $addingVerbsSql,
                $params
            );

        $writer = new ManagerWriter($this->dbConnection);
        $writer->add($payload, $this->verb);
    }


    private function getVerbPayload(): MockObject|Payload
    {
        $expectedArray = VerbProvider::managedVerbToGoAsArray();
        $payload = $this->createMock(Payload::class);
        $payload->expects($this->once())
            ->method('asArray')
            ->willReturn($expectedArray);

        return $payload;
    }


    public function testCanUpdateWord(): void
    {
        $payload = $this->getWordPayload();

        $sql = EditWordsSql::create();
        $params = Parameters::init();
        $params->addString('Schärenküste');
        $params->addString('skjærgård');
        $params->addInt(3);

        $this->dbConnection->expects($this->once())
            ->method('execute')
            ->with(
                $sql,
                $params
            )
            ->willReturn(AffectedRows::fromInt(1));

        $writer = new ManagerWriter($this->dbConnection);
        $writer->update($this->id, $payload, $this->word);
    }


    public function testCanUpdateVerb(): void
    {
        $payload = $this->getVerbPayload();

        $sql = EditVerbsSql::create();
        $params = Parameters::init();
        $params->addString('gehen');
        $params->addString('gå');
        $params->addString('går');
        $params->addString('gikk');
        $params->addString('har gått');
        $params->addInt(3);

        $this->dbConnection->expects($this->once())
            ->method('execute')
            ->with(
                $sql,
                $params
            )
            ->willReturn(AffectedRows::fromInt(1));

        $writer = new ManagerWriter($this->dbConnection);
        $writer->update($this->id, $payload, $this->verb);
    }


    public function testThrowsExceptionIfNoWordToUpdateWasFound(): void
    {
        $this->expectExceptionObject(new NoRecordInDatabaseException($this->id));

        $payload = $this->getWordPayload();

        $sql = EditWordsSql::create();
        $params = Parameters::init();
        $params->addString('Schärenküste');
        $params->addString('skjærgård');
        $params->addInt(3);

        $this->dbConnection->expects($this->once())
            ->method('execute')
            ->with(
                $sql,
                $params
            )
            ->willReturn(AffectedRows::fromInt(0));

        $writer = new ManagerWriter($this->dbConnection);
        $writer->update($this->id, $payload, $this->word);
    }


    public function testCanRemoveWord(): void
    {
        $sql = RemoveVocabularySql::create($this->word);
        $params = Parameters::init();
        $params->addInt(3);

        $this->dbConnection->expects($this->once())
            ->method('execute')
            ->with(
                $sql,
                $params
            )
            ->willReturn(AffectedRows::fromInt(1));

        $writer = new ManagerWriter($this->dbConnection);
        $writer->remove($this->id, $this->word);
    }


    public function testCanRemoveVerb(): void
    {
        $sql = RemoveVocabularySql::create($this->verb);
        $params = Parameters::init();
        $params->addInt(3);

        $this->dbConnection->expects($this->once())
            ->method('execute')
            ->with(
                $sql,
                $params
            )
            ->willReturn(AffectedRows::fromInt(1));

        $writer = new ManagerWriter($this->dbConnection);
        $writer->remove($this->id, $this->verb);
    }


    public function testThrowsExceptionIfNoWordToDeleteWasFound(): void
    {
        $this->expectExceptionObject(new NoRecordInDatabaseException($this->id));

        $sql = RemoveVocabularySql::create($this->word);
        $params = Parameters::init();
        $params->addInt(3);

        $this->dbConnection->expects($this->once())
            ->method('execute')
            ->with(
                $sql,
                $params
            )
            ->willReturn(AffectedRows::fromInt(0));

        $writer = new ManagerWriter($this->dbConnection);
        $writer->remove($this->id, $this->word);
    }


    protected function setUp(): void
    {
        $this->dbConnection = $this->createMock(DbConnection::class);
        $this->id = Id::by(3);
        $this->word = VocabularyType::word;
        $this->verb = VocabularyType::verb;
    }
}
