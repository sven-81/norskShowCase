<?php

declare(strict_types=1);

namespace norsk\api\trainer\words;

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
use norsk\api\trainer\words\queries\GetAllWordsForUserSql;
use norsk\api\user\UserName;

class WordReader
{
    private readonly GetAllWordsForUserSql $allWordsForUser;


    public function __construct(private readonly DbConnection $dbConnector)
    {
        $this->allWordsForUser = GetAllWordsForUserSql::create();
    }


    public function getAllWordsFor(UserName $userName): Vocabularies
    {
        $params = Parameters::init();
        $params->addString($userName->asString());

        $result = $this->dbConnector->getResult(
            $this->allWordsForUser,
            $params
        );

        $this->ensureDatabaseHasAnyWords($result);

        $words = Vocabularies::create();
        foreach ($result as $wordRecord) {
            $word = TrainingWord::of(
                Id::by($wordRecord['id']),
                German::of($wordRecord['german']),
                Norsk::of($wordRecord['norsk']),
                SuccessCounter::by($wordRecord['successCounter'])
            );
            $words->add($word);
        }

        return $words;
    }


    private function ensureDatabaseHasAnyWords(SqlResult $result): void
    {
        if ($result->count() < 1) {
            throw new NoRecordInDatabaseException(
                'No records found in database for: words',
                ResponseCode::serverError->value
            );
        }
    }
}
