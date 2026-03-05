<?php

declare(strict_types=1);

namespace norsk\api\manager\infrastructure\persistence;

use norsk\api\infrastructure\persistence\DbConnection;
use norsk\api\infrastructure\persistence\Parameters;
use norsk\api\infrastructure\persistence\SqlResult;
use norsk\api\manager\domain\ManagedVocabularies;
use norsk\api\manager\domain\verbs\ManagedVerb;
use norsk\api\manager\infrastructure\persistence\queries\verbs\GetAllVerbsSql;
use norsk\api\shared\domain\German;
use norsk\api\shared\domain\Id;
use norsk\api\shared\domain\Norsk;
use norsk\api\trainer\domain\exceptions\NoRecordInDatabaseException;
use norsk\api\trainer\domain\verbs\ManagingVerbReadingRepository;

readonly class VerbReader implements ManagingVerbReadingRepository
{
    private GetAllVerbsSql $allVerbs;


    public function __construct(private DbConnection $dbConnector)
    {
        $this->allVerbs = GetAllVerbsSql::create();
    }


    public function getAllVerbs(): ManagedVocabularies
    {
        $params = Parameters::init();

        $result = $this->dbConnector->getResult($this->allVerbs, $params);

        $this->ensureDatabaseHasAnyVerbs($result);

        $verbs = ManagedVocabularies::create();
        foreach ($result as $verbRecord) {
            $verbs->add(ManagedVerb::fromPersistence(
                Id::by($verbRecord['id']),
                German::of($verbRecord['german']),
                Norsk::of($verbRecord['norsk']),
                Norsk::of($verbRecord['norsk_present']),
                Norsk::of($verbRecord['norsk_past']),
                Norsk::of($verbRecord['norsk_past_perfekt'])
            ));
        }

        return $verbs;
    }


    private function ensureDatabaseHasAnyVerbs(SqlResult $result): void
    {
        if ($result->count() < 1) {
            throw new NoRecordInDatabaseException('No records found in database for: verbs');
        }
    }
}
