<?php

declare(strict_types=1);

namespace norsk\api\trainer\verbs;

use norsk\api\app\persistence\DbConnection;
use norsk\api\app\persistence\Parameters;
use norsk\api\app\persistence\SqlResult;
use norsk\api\app\response\ResponseCode;
use norsk\api\shared\German;
use norsk\api\shared\Id;
use norsk\api\shared\Norsk;
use norsk\api\shared\Vocabularies;
use norsk\api\trainer\exceptions\NoRecordInDatabaseException;
use norsk\api\trainer\SuccessCounter;
use norsk\api\trainer\verbs\queries\GetAllVerbsForUserSql;
use norsk\api\user\UserName;

class VerbReader
{
    private readonly GetAllVerbsForUserSql $allVerbsForUser;


    public function __construct(private readonly DbConnection $dbConnector)
    {
        $this->allVerbsForUser = GetAllVerbsForUserSql::create();
    }


    public function getAllVerbsFor(UserName $userName): Vocabularies
    {
        $params = Parameters::init();
        $params->addString($userName->asString());

        $result = $this->dbConnector->getResult(
            $this->allVerbsForUser,
            $params
        );

        $this->ensureDatabaseHasAnyVerbs($result);

        $verbs = Vocabularies::create();
        foreach ($result as $verbRecord) {
            $verb = TrainingVerb::of(
                Id::by($verbRecord['id']),
                German::of($verbRecord['german']),
                Norsk::of($verbRecord['norsk']),
                Norsk::of($verbRecord['norsk_present']),
                Norsk::of($verbRecord['norsk_past']),
                Norsk::of($verbRecord['norsk_past_perfekt']),
                SuccessCounter::by($verbRecord['successCounter'])
            );
            $verbs->add($verb);
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
