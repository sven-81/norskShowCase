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
use norsk\api\shared\German;
use norsk\api\shared\Id;
use norsk\api\shared\Norsk;
use norsk\api\shared\Vocabularies;
use norsk\api\shared\VocabularyType;
use norsk\api\trainer\exceptions\NoRecordInDatabaseException;

class VerbReader
{
    private readonly GetAllVerbsSql $allVerbs;

    private readonly VocabularyType $vocabularyType;


    public function __construct(private readonly DbConnection $dbConnector)
    {
        $this->allVerbs = GetAllVerbsSql::create();
        $this->vocabularyType = VocabularyType::verb;
    }


    public function getAllVerbs(): Vocabularies
    {
        $params = Parameters::init();

        $result = $this->dbConnector->getResult(
            $this->allVerbs,
            $params
        );

        $this->ensureDatabaseHasAnyVerbs($result);

        $verbs = Vocabularies::create();
        foreach ($result as $verbRecord) {
            $word = ManagedVerb::of(
                Id::by($verbRecord['id']),
                German::of($verbRecord['german']),
                Norsk::of($verbRecord['norsk']),
                Norsk::of($verbRecord['norsk_present']),
                Norsk::of($verbRecord['norsk_past']),
                Norsk::of($verbRecord['norsk_past_perfekt'])
            );
            $verbs->add($word);
        }

        return $verbs;
    }


    private function ensureDatabaseHasAnyVerbs(SqlResult $result): void
    {
        if ($result->count() < 1) {
            throw new NoRecordInDatabaseException(
                'No records found in database for: verbs',
                ResponseCode::serverError->value
            );
        }
    }


    public function ensureVerbsAreNotAlreadyPersisted(?Id $id, Payload $payload): void
    {
        $payloadArray = $payload->asArray();
        $german = German::of($payloadArray['german']);
        $norsk = Norsk::of($payloadArray['norsk']);

        $params = Parameters::init();
        $params->addString($german->asString());
        $alreadyExistingGermanSql = LookForExistingGermanVocabularySql::create($this->vocabularyType, $id);
        $germanResult = $this->dbConnector->getResult($alreadyExistingGermanSql, $params);

        $params->addString($norsk->asString());
        $alreadyExistingTupleSql = LookForExistingGermanAndNorskVocabularyTupleSql::create($this->vocabularyType, $id);
        $tupleResult = $this->dbConnector->getResult(
            $alreadyExistingTupleSql,
            $params
        );

        $identifier = $this->getIdentifier($id, $payload);
        $this->ensureVerbTupleIsUnique($tupleResult, $identifier);
        $this->ensureGermanIsUnique($germanResult, $identifier);
    }


    private function getIdentifier(?Id $id, Payload $payload): Identifier
    {
        if ($id instanceof Id) {
            return Identifier::fromId($id);
        }

        return Identifier::fromPayload($payload);
    }


    private function ensureVerbTupleIsUnique(SqlResult $tupleResult, Identifier $identifier): void
    {
        if ($tupleResult->hasEntries()) {
            throw new RecordAlreadyInDatabaseException($identifier, $this->vocabularyType);
        }
    }


    private function ensureGermanIsUnique(SqlResult $germanResult, Identifier $identifier): void
    {
        if ($germanResult->hasEntries()) {
            throw new GermanRecordAlreadyInDatabaseException($identifier, $this->vocabularyType);
        }
    }
}
