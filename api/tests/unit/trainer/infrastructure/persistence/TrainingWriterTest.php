<?php

declare(strict_types=1);

namespace norsk\api\trainer\infrastructure\persistence;

use norsk\api\infrastructure\persistence\AffectedRows;
use norsk\api\infrastructure\persistence\DbConnection;
use norsk\api\infrastructure\persistence\Parameters;
use norsk\api\infrastructure\persistence\SqlStatement;
use norsk\api\shared\domain\Id;
use norsk\api\shared\domain\VocabularyType;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\trainer\domain\exceptions\NoRecordInDatabaseException;
use norsk\api\trainer\infrastructure\persistence\queries\verbs\SaveTrainedVerbSql;
use norsk\api\trainer\infrastructure\persistence\queries\words\SaveTrainedWordSql;
use norsk\api\user\domain\valueObjects\UserName;
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


    public static function provideSaveMethods(): array
    {
        return [
            'word' => [
                'saveAsTrainedWord',
                SaveTrainedWordSql::create(),
                VocabularyType::word,
            ],
            'verb' => [
                'saveAsTrainedVerb',
                SaveTrainedVerbSql::create(),
                VocabularyType::verb,
            ],
        ];
    }


    #[DataProvider('provideSaveMethods')]
    public function testCanSaveTrainedWord(
        string $methodName,
        SqlStatement $expectedSql,
        VocabularyType $type
    ): void {
        $this->dbConnectionMock->expects($this->once())
            ->method('execute')
            ->with(
                $expectedSql,
                $this->params
            )
            ->willReturn(AffectedRows::fromInt(1));

        $writer = new TrainingWriter($this->dbConnectionMock);
        $writer->$methodName($this->userName, $this->id);
    }


    private function getSql(VocabularyType $vocabularyType): SaveTrainedWordSql|SaveTrainedVerbSql
    {
        if ($vocabularyType->isWord($vocabularyType)) {
            return SaveTrainedWordSql::create();
        }

        return SaveTrainedVerbSql::create();
    }


    #[DataProvider('provideSaveMethods')]
    public function testThrowsExceptionIfTrainedWordWasInactive(
        string $methodName,
        SqlStatement $expectedSql,
        VocabularyType $type
    ): void {
        $this->expectExceptionObject(
            new NoRecordInDatabaseException(
                'No record found in database for ' . $type->value . 'Id: ' . $this->id->asString(),
                ResponseCode::notFound->value
            )
        );

        $this->dbConnectionMock->expects($this->once())
            ->method('execute')
            ->with(
                $expectedSql,
                $this->params
            )
            ->willReturn(AffectedRows::fromInt(0));

        $writer = new TrainingWriter($this->dbConnectionMock);
        $writer->$methodName($this->userName, $this->id);
    }


    #[DataProvider('provideSaveMethods')]
    public function testThrowsExceptionIfTrainedWordWasNotFound(
        string $methodName,
        SqlStatement $expectedSql,
        VocabularyType $type
    ): void {
        $throwable = new RuntimeException('oops', ResponseCode::serverError->value);
        $this->expectExceptionObject(
            new RuntimeException(
                'Failed to save trained ' . $type->value . ': ' . $throwable->getMessage()
            )
        );

        $this->dbConnectionMock->expects($this->once())
            ->method('execute')
            ->willThrowException($throwable);

        $writer = new TrainingWriter($this->dbConnectionMock);
        $writer->$methodName($this->userName, $this->id);
    }
}
