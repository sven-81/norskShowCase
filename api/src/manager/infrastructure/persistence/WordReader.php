<?php

declare(strict_types=1);

namespace norsk\api\manager\infrastructure\persistence;

use norsk\api\infrastructure\persistence\DbConnection;
use norsk\api\infrastructure\persistence\Parameters;
use norsk\api\infrastructure\persistence\SqlResult;
use norsk\api\manager\domain\words\ManagedWord;
use norsk\api\manager\infrastructure\persistence\queries\words\GetAllWordsSql;
use norsk\api\shared\domain\German;
use norsk\api\shared\domain\Id;
use norsk\api\shared\domain\Norsk;
use norsk\api\shared\domain\Vocabularies;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\trainer\domain\exceptions\NoRecordInDatabaseException;
use norsk\api\trainer\domain\words\ManagingWordReadingRepository;

class WordReader implements ManagingWordReadingRepository
{
    private readonly GetAllWordsSql $allWords;


    public function __construct(private readonly DbConnection $dbConnector)
    {
        $this->allWords = GetAllWordsSql::create();
    }


    public function getAllWords(): Vocabularies
    {
        $params = Parameters::init();

        $result = $this->dbConnector->getResult(
            $this->allWords,
            $params
        );

        $this->ensureDatabaseHasAnyWords($result);

        $words = Vocabularies::create();
        foreach ($result as $wordRecord) {
            $word = ManagedWord::fromPersistence(
                Id::by($wordRecord['id']),
                German::of($wordRecord['german']),
                Norsk::of($wordRecord['norsk'])
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
