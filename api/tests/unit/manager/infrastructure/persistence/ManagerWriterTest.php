<?php

declare(strict_types=1);

namespace norsk\api\manager\infrastructure\persistence;

use norsk\api\infrastructure\persistence\AffectedRows;
use norsk\api\infrastructure\persistence\DbConnection;
use norsk\api\infrastructure\persistence\Parameters;
use norsk\api\manager\domain\exceptions\NoRecordInDatabaseException;
use norsk\api\manager\domain\verbs\ManagedVerb;
use norsk\api\manager\domain\words\ManagedWord;
use norsk\api\manager\infrastructure\persistence\queries\RemoveVocabularySql;
use norsk\api\manager\infrastructure\persistence\queries\verbs\AddingVerbsSql;
use norsk\api\manager\infrastructure\persistence\queries\verbs\EditVerbsSql;
use norsk\api\manager\infrastructure\persistence\queries\words\AddingWordsSql;
use norsk\api\manager\infrastructure\persistence\queries\words\EditWordsSql;
use norsk\api\shared\domain\Id;
use norsk\api\shared\domain\VocabularyType;
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

    private ManagedWord $word;

    private ManagedVerb $verb;

    private VocabularyType $typeVerb;

    private VocabularyType $typeWord;


    protected function setUp(): void
    {
        $this->dbConnection = $this->createMock(DbConnection::class);
        $this->id = Id::by(3);
        $this->word = WordProvider::managedWordArchipelago();
        $this->verb = VerbProvider::managedVerbToGo();
        $this->typeVerb = VocabularyType::verb;
        $this->typeWord = VocabularyType::word;
    }


    public function testCanAddWord(): void
    {
        $addingWordsSql = AddingWordsSql::create();
        $params = Parameters::init();
        $params->addString($this->word->getGerman()->asString());
        $params->addString($this->word->getNorsk()->asString());

        $this->dbConnection->expects($this->once())
            ->method('execute')
            ->with(
                $addingWordsSql,
                $params
            );

        $writer = new ManagerWriter($this->dbConnection);
        $writer->add($this->word);
    }


    public function testCanAddVerb(): void
    {
        $addingVerbsSql = AddingVerbsSql::create();
        $params = Parameters::init();
        $params->addString($this->verb->getGerman()->asString());
        $params->addString($this->verb->getNorsk()->asString());
        $params->addString($this->verb->getNorskPresent()->asString());
        $params->addString($this->verb->getNorskPast()->asString());
        $params->addString($this->verb->getNorskPastPerfect()->asString());

        $this->dbConnection->expects($this->once())
            ->method('execute')
            ->with(
                $addingVerbsSql,
                $params
            );

        $writer = new ManagerWriter($this->dbConnection);
        $writer->add($this->verb);
    }


    public function testCanUpdateWord(): void
    {
        $sql = EditWordsSql::create();
        $params = Parameters::init();
        $params->addString($this->word->getGerman()->asString());
        $params->addString($this->word->getNorsk()->asString());
        $params->addInt(3);

        $this->dbConnection->expects($this->once())
            ->method('execute')
            ->with(
                $sql,
                $params
            )
            ->willReturn(AffectedRows::fromInt(1));

        $writer = new ManagerWriter($this->dbConnection);
        $writer->update($this->word);
    }


    public function testCanUpdateVerb(): void
    {
        $sql = EditVerbsSql::create();
        $params = Parameters::init();
        $params->addString($this->verb->getGerman()->asString());
        $params->addString($this->verb->getNorsk()->asString());
        $params->addString($this->verb->getNorskPresent()->asString());
        $params->addString($this->verb->getNorskPast()->asString());
        $params->addString($this->verb->getNorskPastPerfect()->asString());
        $params->addInt(1);

        $this->dbConnection->expects($this->once())
            ->method('execute')
            ->with(
                $sql,
                $params
            )
            ->willReturn(AffectedRows::fromInt(1));

        $writer = new ManagerWriter($this->dbConnection);
        $writer->update($this->verb);
    }


    public function testThrowsExceptionIfNoWordToUpdateWasFound(): void
    {
        $this->expectExceptionObject(new NoRecordInDatabaseException($this->id));

        $sql = EditWordsSql::create();
        $params = Parameters::init();
        $params->addString($this->word->getGerman()->asString());
        $params->addString($this->word->getNorsk()->asString());
        $params->addInt(3);

        $this->dbConnection->expects($this->once())
            ->method('execute')
            ->with(
                $sql,
                $params
            )
            ->willReturn(AffectedRows::fromInt(0));

        $writer = new ManagerWriter($this->dbConnection);
        $writer->update($this->word);
    }


    public function testCanRemoveWord(): void
    {
        $sql = RemoveVocabularySql::create($this->typeWord);
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
        $writer->remove($this->id, $this->typeWord);
    }


    public function testCanRemoveVerb(): void
    {
        $sql = RemoveVocabularySql::create($this->typeVerb);
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
        $writer->remove($this->id, $this->typeVerb);
    }


    public function testThrowsExceptionIfNoWordToDeleteWasFound(): void
    {
        $this->expectExceptionObject(new NoRecordInDatabaseException($this->id));

        $sql = RemoveVocabularySql::create($this->typeWord);
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
        $writer->remove($this->id, $this->typeWord);
    }
}
