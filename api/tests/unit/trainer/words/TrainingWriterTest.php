<?php

declare(strict_types=1);

namespace norsk\api\trainer\words;

use norsk\api\app\persistence\AffectedRows;
use norsk\api\app\persistence\DbConnection;
use norsk\api\app\persistence\Parameters;
use norsk\api\app\response\ResponseCode;
use norsk\api\shared\Id;
use norsk\api\shared\VocabularyType;
use norsk\api\tests\provider\VocabularyTypeProvider;
use norsk\api\trainer\exceptions\NoRecordInDatabaseException;
use norsk\api\trainer\TrainingWriter;
use norsk\api\trainer\verbs\queries\SaveTrainedVerbSql;
use norsk\api\trainer\words\queries\SaveTrainedWordSql;
use norsk\api\user\UserName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(TrainingWriter::class)]
class TrainingWriterTest extends TestCase
{
    private DbConnection|MockObject $dbConnectionMock;

    private Id $id;

    private UserName $userName;

    private Parameters $params;


    public static function getVocabularyType(): array
    {
        return VocabularyTypeProvider::getVocabularyType();
    }


    #[DataProvider('getVocabularyType')]
    public function testCanSaveTrainedWord(VocabularyType $vocabularyType): void
    {
        $this->dbConnectionMock->expects($this->once())
            ->method('execute')
            ->with(
                $this->getSql($vocabularyType),
                $this->params
            )
            ->willReturn(AffectedRows::fromInt(1));

        $writer = new TrainingWriter($this->dbConnectionMock);
        $writer->save($this->userName, $this->id, $vocabularyType);
    }


    private function getSql(VocabularyType $vocabularyType): SaveTrainedWordSql|SaveTrainedVerbSql
    {
        if ($vocabularyType->isWord($vocabularyType)) {
            return SaveTrainedWordSql::create();
        }

        return SaveTrainedVerbSql::create();
    }


    #[DataProvider('getVocabularyType')]
    public function testThrowsExceptionIfTrainedWordWasInactive(VocabularyType $vocabularyType): void
    {
        $this->expectExceptionObject(
            new NoRecordInDatabaseException(
                'No record found in database for ' . $vocabularyType->value . 'Id: ' . $this->id->asString(),
                ResponseCode::notFound->value
            )
        );

        $this->dbConnectionMock->expects($this->once())
            ->method('execute')
            ->with(
                $this->getSql($vocabularyType),
                $this->params
            )
            ->willReturn(AffectedRows::fromInt(0));

        $writer = new TrainingWriter($this->dbConnectionMock);
        $writer->save($this->userName, $this->id, $vocabularyType);
    }


    #[DataProvider('getVocabularyType')]
    public function testThrowsExceptionIfTrainedWordWasNotFound(VocabularyType $vocabularyType): void
    {
        $throwable = new RuntimeException('oops', ResponseCode::serverError->value);
        $this->expectExceptionObject(
            new RuntimeException(
                'Failed to save trained ' . $vocabularyType->value . ': ' . $throwable->getMessage()
            )
        );

        $this->dbConnectionMock->expects($this->once())
            ->method('execute')
            ->willThrowException($throwable);

        $writer = new TrainingWriter($this->dbConnectionMock);
        $writer->save($this->userName, $this->id, $vocabularyType);
    }


    protected function setUp(): void
    {
        $this->dbConnectionMock = $this->createMock(DbConnection::class);
        $this->id = Id::by(1);
        $this->userName = UserName::by('someUser');

        $this->configureParams();
    }


    private function configureParams(): void
    {
        $this->params = Parameters::init();
        $this->params->addString($this->userName->asString());
        $this->params->addInt($this->id->asInt());
        $this->params->addInt($this->id->asInt());
    }
}
