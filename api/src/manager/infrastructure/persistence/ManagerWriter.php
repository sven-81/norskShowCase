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
use norsk\api\shared\domain\ManagingVocabulary;
use norsk\api\shared\domain\Norsk;
use norsk\api\shared\domain\VocabularyPersistencePort;
use norsk\api\shared\domain\VocabularyType;

readonly class ManagerWriter implements WritingRepository, VocabularyPersistencePort
{
    private EditWordsSql $editWords;

    private AddingWordsSql $addingWordsSql;

    private AddingVerbsSql $addingVerbsSql;

    private EditVerbsSql $editVerbs;


    public function __construct(private DbConnection $dbConnector)
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
        $vocabulary->updateWith($this);
    }


    public function saveEditedWord(ManagedWord $word): void
    {
        $params = $this->addGermanAndNorskInfinitive($word->getGerman(), $word->getNorsk());
        $params->addInt($word->getId()->asInt());

        $affectedRows = $this->dbConnector->execute(
            $this->editWords,
            $params
        );

        $this->ensureIdWasFoundInDatabase($affectedRows, $word->getId());
    }


    public function saveEditedVerb(ManagedVerb $verb): void
    {
        $params = $this->addVerbToParams($verb);
        $params->addInt($verb->getId()->asInt());

        $affectedRows = $this->dbConnector->execute(
            $this->editVerbs,
            $params
        );

        $this->ensureIdWasFoundInDatabase($affectedRows, $verb->getId());
    }


    private function ensureIdWasFoundInDatabase(AffectedRows $affectedRows, Id $id): void
    {
        if ($affectedRows->notAtLeastOne()) {
            throw new NoRecordInDatabaseException($id);
        }
    }


    public function remove(Id $id, VocabularyType $vocabularyType): void
    {
        $params = Parameters::init();
        $params->addInt($id->asInt());

        $affectedRows = $this->dbConnector->execute(
            RemoveVocabularySql::create($vocabularyType),
            $params
        );

        $this->ensureIdWasFoundInDatabase($affectedRows, $id);
    }
}
