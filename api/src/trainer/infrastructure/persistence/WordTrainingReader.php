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
use norsk\api\trainer\domain\words\TrainingWord;
use norsk\api\trainer\domain\words\TrainingWordReadingRepository;
use norsk\api\trainer\infrastructure\persistence\queries\words\GetAllWordsForUserSql;
use norsk\api\user\domain\valueObjects\UserName;

class WordTrainingReader implements TrainingWordReadingRepository
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
