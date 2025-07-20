<?php

declare(strict_types=1);

namespace norsk\api\manager\infrastructure\persistence;

use norsk\api\infrastructure\persistence\AffectedRows;
use norsk\api\infrastructure\persistence\DbConnection;
use norsk\api\infrastructure\persistence\Parameters;
use norsk\api\manager\domain\exceptions\NoRecordInDatabaseException;
use norsk\api\manager\domain\verbs\ManagedVerb;
use norsk\api\manager\domain\words\ManagedWord;
use norsk\api\manager\domain\WritingRepository;
use norsk\api\manager\infrastructure\persistence\queries\RemoveVocabularySql;
use norsk\api\manager\infrastructure\persistence\queries\verbs\AddingVerbsSql;
use norsk\api\manager\infrastructure\persistence\queries\verbs\EditVerbsSql;
use norsk\api\manager\infrastructure\persistence\queries\words\AddingWordsSql;
use norsk\api\manager\infrastructure\persistence\queries\words\EditWordsSql;
use norsk\api\shared\domain\German;
use norsk\api\shared\domain\Id;
use norsk\api\shared\domain\Norsk;
use norsk\api\shared\domain\ManagingVocabulary;
use norsk\api\shared\domain\VocabularyPersistencePort;
use norsk\api\shared\domain\VocabularyType;

class ManagerWriter implements WritingRepository, VocabularyPersistencePort
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


    public function add(ManagingVocabulary $vocabulary): void
    {
        $vocabulary->persistWith($this);
    }


    public function saveNewWord(ManagedWord $word): void
    {
        $params = $this->addGermanAndNorskInfinitive($word->getGerman(), $word->getNorsk());

        $this->dbConnector->execute(
            $this->addingWordsSql,
            $params
        );
    }


    private function addGermanAndNorskInfinitive(German $german, Norsk $norsk): Parameters
    {
        $params = Parameters::init();
        $params->addString($german->asString());
        $params->addString($norsk->asString());

        return $params;
    }


    public function saveNewVerb(ManagedVerb $verb): void
    {
        $params = $this->addVerbToParams($verb);

        $this->dbConnector->execute(
            $this->addingVerbsSql,
            $params
        );
    }


    private function addVerbToParams(ManagedVerb $vocabulary): Parameters
    {
        $params = $this->addGermanAndNorskInfinitive($vocabulary->getGerman(), $vocabulary->getNorsk());
        $params->addString($vocabulary->getNorskPresent()->asString());
        $params->addString($vocabulary->getNorskPast()->asString());
        $params->addString($vocabulary->getNorskPastPerfect()->asString());

        return $params;
    }


    public function update(ManagingVocabulary $vocabulary): void
    {
        $changedRows = $vocabulary->updateWith($this);

        $this->ensureIdToChangeWasInDatabase($changedRows, $vocabulary->getId());
    }


    public function saveEditedWord(ManagedWord $word): AffectedRows
    {
        $params = $this->addGermanAndNorskInfinitive($word->getGerman(), $word->getNorsk());
        $params->addInt($word->getId()->asInt());

        return $this->dbConnector->execute(
            $this->editWords,
            $params
        );
    }


    public function saveEditedVerb(ManagedVerb $verb): AffectedRows
    {
        $params = $this->addVerbToParams($verb);
        $params->addInt($verb->getId()->asInt());

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
