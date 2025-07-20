<?php

declare(strict_types=1);

namespace norsk\api\trainer\infrastructure\persistence;

use norsk\api\infrastructure\persistence\DbConnection;
use norsk\api\infrastructure\persistence\Parameters;
use norsk\api\infrastructure\persistence\SqlResult;
use norsk\api\shared\domain\German;
use norsk\api\shared\domain\Id;
use norsk\api\shared\domain\Norsk;
use norsk\api\shared\domain\Vocabularies;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\trainer\domain\exceptions\NoRecordInDatabaseException;
use norsk\api\trainer\domain\SuccessCounter;
use norsk\api\trainer\domain\verbs\TrainingVerb;
use norsk\api\trainer\domain\verbs\TrainingVerbReadingRepository;
use norsk\api\trainer\infrastructure\persistence\queries\verbs\GetAllVerbsForUserSql;
use norsk\api\user\domain\valueObjects\UserName;

class VerbTrainingReader implements TrainingVerbReadingRepository
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
