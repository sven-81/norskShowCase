<?php

declare(strict_types=1);

namespace norsk\api\manager\infrastructure\persistence;

use norsk\api\infrastructure\persistence\DbConnection;
use norsk\api\infrastructure\persistence\Parameters;
use norsk\api\infrastructure\persistence\SqlResult;
use norsk\api\manager\domain\exceptions\GermanRecordAlreadyInDatabaseException;
use norsk\api\manager\domain\exceptions\RecordAlreadyInDatabaseException;
use norsk\api\manager\domain\Identifier;
use norsk\api\manager\domain\VocabularyUniquenessPolicy;
use norsk\api\manager\infrastructure\persistence\queries\LookForExistingGermanAndNorskVocabularyTupleSql;
use norsk\api\manager\infrastructure\persistence\queries\LookForExistingGermanVocabularySql;
use norsk\api\shared\domain\German;
use norsk\api\shared\domain\Id;
use norsk\api\shared\domain\Norsk;
use norsk\api\shared\domain\VocabularyType;

readonly class SqlUniquenessPolicy implements VocabularyUniquenessPolicy
{
    public function __construct(private DbConnection $dbConnector, private VocabularyType $vocabularyType)
    {
    }


    public function ensureVocabularyIsNotAlreadyPersisted(?Id $id, German $german, Norsk $norsk): void
    {
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

        $identifier = $this->getIdentifier($id, $german, $norsk);
        $this->ensureVocabularyTupleIsUnique($tupleResult, $identifier);
        $this->ensureGermanIsUnique($germanResult, $identifier);
    }


    private function getIdentifier(?Id $id, German $german, Norsk $norsk): Identifier
    {
        if ($id instanceof Id) {
            return Identifier::fromId($id);
        }

        return Identifier::fromVocabulary($german, $norsk);
    }


    private function ensureVocabularyTupleIsUnique(SqlResult $tupleResult, Identifier $identifier): void
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