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
use norsk\api\shared\German;
use norsk\api\shared\Id;
use norsk\api\shared\Norsk;
use norsk\api\shared\VocabularyType;

class ManagerWriter
{
    private readonly EditWordsSql $editWords;

    private readonly AddingWordsSql $addingWordsSql;

    private readonly AddingVerbsSql $addingVerbsSql;

    private readonly EditVerbsSql $editVerbs;


    public function __construct(private readonly DbConnection $dbConnector)
    {
        $this->addingWordsSql = AddingWordsSql::create();
        $this->addingVerbsSql = AddingVerbsSql::create();
        $this->editWords = EditWordsSql::create();
        $this->editVerbs = EditVerbsSql::create();
    }


    public function add(Payload $payload, VocabularyType $vocabularyType): void
    {
        $payloadArray = $payload->asArray();

        if ($vocabularyType->isWord($vocabularyType)) {
            $this->saveNewWord($payloadArray);
        } else {
            $this->saveNewVerb($payloadArray);
        }
    }


    private function saveNewWord(array $payloadArray): void
    {
        $params = $this->addGermanAndNorskInfinitive($payloadArray);

        $this->dbConnector->execute(
            $this->addingWordsSql,
            $params
        );
    }


    private function addGermanAndNorskInfinitive(array $payloadArray): Parameters
    {
        $german = German::of($payloadArray['german']);
        $norsk = Norsk::of($payloadArray['norsk']);

        $params = Parameters::init();
        $params->addString($german->asString());
        $params->addString($norsk->asString());

        return $params;
    }


    private function saveNewVerb(array $payloadArray): void
    {
        $params = $this->addVerbToParams($payloadArray);

        $this->dbConnector->execute(
            $this->addingVerbsSql,
            $params
        );
    }


    private function addVerbToParams(array $payloadArray): Parameters
    {
        $norskPresent = Norsk::of($payloadArray['norskPresent']);
        $norskPast = Norsk::of($payloadArray['norskPast']);
        $norskPastPerfect = Norsk::of($payloadArray['norskPastPerfect']);

        $params = $this->addGermanAndNorskInfinitive($payloadArray);
        $params->addString($norskPresent->asString());
        $params->addString($norskPast->asString());
        $params->addString($norskPastPerfect->asString());

        return $params;
    }


    public function update(Id $id, Payload $payload, VocabularyType $vocabularyType): void
    {
        $payloadArray = $payload->asArray();

        if ($vocabularyType->isWord($vocabularyType)) {
            $changedRows = $this->saveEditedWord($payloadArray, $id);
        } else {
            $changedRows = $this->saveEditedVerb($payloadArray, $id);
        }

        $this->ensureIdToChangeWasInDatabase($changedRows, $id);
    }


    private function saveEditedWord(array $payloadArray, Id $id): AffectedRows
    {
        $params = $this->addGermanAndNorskInfinitive($payloadArray);
        $params->addInt($id->asInt());

        return $this->dbConnector->execute(
            $this->editWords,
            $params
        );
    }


    private function saveEditedVerb(array $payloadArray, Id $id): AffectedRows
    {
        $params = $this->addVerbToParams($payloadArray);
        $params->addInt($id->asInt());

        return $this->dbConnector->execute(
            $this->editVerbs,
            $params
        );
    }


    private function ensureIdToChangeWasInDatabase(AffectedRows $changedRows, Id $id): void
    {
        if ($changedRows->notAtLeastOne()) {
            throw new NoRecordInDatabaseException($id);
        }
    }


    public function remove(Id $id, VocabularyType $vocabularyType): void
    {
        $params = Parameters::init();
        $params->addInt($id->asInt());

        $changedRows = $this->dbConnector->execute(
            RemoveVocabularySql::create($vocabularyType),
            $params
        );

        $this->ensureIdToChangeWasInDatabase($changedRows, $id);
    }
}
