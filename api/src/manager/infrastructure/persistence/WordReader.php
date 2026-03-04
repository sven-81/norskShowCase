<?php

declare(strict_types=1);

namespace norsk\api\manager\infrastructure\persistence;

use norsk\api\infrastructure\persistence\DbConnection;
use norsk\api\infrastructure\persistence\Parameters;
use norsk\api\infrastructure\persistence\SqlResult;
use norsk\api\manager\domain\ManagedVocabularies;
use norsk\api\manager\domain\words\ManagedWord;
use norsk\api\manager\infrastructure\persistence\queries\words\GetAllWordsSql;
use norsk\api\shared\domain\German;
use norsk\api\shared\domain\Id;
use norsk\api\shared\domain\Norsk;
use norsk\api\trainer\domain\exceptions\NoRecordInDatabaseException;
use norsk\api\trainer\domain\words\ManagingWordReadingRepository;

readonly class WordReader implements ManagingWordReadingRepository
{
    private GetAllWordsSql $allWords;


    public function __construct(private DbConnection $dbConnector)
    {
        $this->allWords = GetAllWordsSql::create();
    }


    public function getAllWords(): ManagedVocabularies
    {
        $params = Parameters::init();

        $result = $this->dbConnector->getResult($this->allWords, $params);

        $this->ensureDatabaseHasAnyWords($result);

        $words = ManagedVocabularies::create();
        foreach ($result as $wordRecord) {
            $words->add(ManagedWord::fromPersistence(
                Id::by($wordRecord['id']),
                German::of($wordRecord['german']),
                Norsk::of($wordRecord['norsk'])
            ));
        }

        return $words;
    }


    private function ensureDatabaseHasAnyWords(SqlResult $result): void
    {
        if ($result->count() < 1) {
            throw new NoRecordInDatabaseException('No records found in database for: words');
        }
    }
}
