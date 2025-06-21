<?php

declare(strict_types=1);

namespace norsk\api\trainer;

use norsk\api\app\persistence\AffectedRows;
use norsk\api\app\persistence\DbConnection;
use norsk\api\app\persistence\Parameters;
use norsk\api\app\response\ResponseCode;
use norsk\api\shared\Id;
use norsk\api\shared\VocabularyType;
use norsk\api\trainer\exceptions\NoRecordInDatabaseException;
use norsk\api\trainer\verbs\queries\SaveTrainedVerbSql;
use norsk\api\trainer\words\queries\SaveTrainedWordSql;
use norsk\api\user\UserName;
use RuntimeException;
use Throwable;

class TrainingWriter
{
    private const int CONSTRAINT_ERROR = 1452;

    private readonly SaveTrainedWordSql $saveTrainedWordSql;

    private readonly SaveTrainedVerbSql $saveTrainedVerbSql;


    public function __construct(private readonly DbConnection $dbConnector)
    {
        $this->saveTrainedWordSql = SaveTrainedWordSql::create();
        $this->saveTrainedVerbSql = SaveTrainedVerbSql::create();
    }


    public function save(UserName $userName, Id $id, VocabularyType $vocabularyType): void
    {
        try {
            $params = Parameters::init();
            $params->addString($userName->asString());
            $params->addInt($id->asInt());
            $params->addInt($id->asInt());

            $sql = $this->getSql($vocabularyType);
            $affectedRows = $this->dbConnector->execute($sql, $params);

            $this->validateRecordWasActive($affectedRows, $id, $vocabularyType);
        } catch (Throwable $throwable) {
            $this->ifNoRecordInDatabaseForId($throwable, $id, $vocabularyType);
            throw new RuntimeException(
                'Failed to save trained ' . $vocabularyType->value . ': ' . $throwable->getMessage()
            );
        }
    }


    private function getSql(VocabularyType $vocabularyType): SaveTrainedVerbSql|SaveTrainedWordSql
    {
        if ($vocabularyType->isWord($vocabularyType)) {
            return $this->saveTrainedWordSql;
        }

        return $this->saveTrainedVerbSql;
    }


    private function validateRecordWasActive(AffectedRows $affectedRows, Id $id, VocabularyType $vocabularyType): void
    {
        if ($affectedRows->notAtLeastOne()) {
            $this->noRecordInDatabaseException($id, $vocabularyType);
        }
    }


    private function noRecordInDatabaseException(Id $id, VocabularyType $vocabularyType): void
    {
        throw new NoRecordInDatabaseException(
            'No record found in database for ' . $vocabularyType->value . 'Id: ' . $id->asString(),
            ResponseCode::notFound->value
        );
    }


    private function ifNoRecordInDatabaseForId(Throwable $throwable, Id $id, VocabularyType $vocabularyType): void
    {
        if (
            $throwable->getCode() === self::CONSTRAINT_ERROR
            || $throwable->getCode() === ResponseCode::notFound->value
        ) {
            $this->noRecordInDatabaseException($id, $vocabularyType);
        }
    }
}
