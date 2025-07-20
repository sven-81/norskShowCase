<?php

declare(strict_types=1);

namespace norsk\api\manager\infrastructure\persistence;

use norsk\api\infrastructure\persistence\DbConnection;
use norsk\api\infrastructure\persistence\Parameters;
use norsk\api\infrastructure\persistence\SqlResult;
use norsk\api\manager\domain\verbs\ManagedVerb;
use norsk\api\manager\infrastructure\persistence\queries\verbs\GetAllVerbsSql;
use norsk\api\shared\domain\German;
use norsk\api\shared\domain\Id;
use norsk\api\shared\domain\Norsk;
use norsk\api\shared\domain\Vocabularies;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\trainer\domain\exceptions\NoRecordInDatabaseException;
use norsk\api\trainer\domain\verbs\ManagingVerbReadingRepository;

class VerbReader implements ManagingVerbReadingRepository
{
    private readonly GetAllVerbsSql $allVerbs;


    public function __construct(private readonly DbConnection $dbConnector)
    {
        $this->allVerbs = GetAllVerbsSql::create();
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
            $word = ManagedVerb::fromPersistence(
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
}
