<?php

declare(strict_types=1);

namespace norsk\api\trainer\infrastructure\persistence\queries\words;

use norsk\api\infrastructure\persistence\SqlStatement;

readonly class GetAllWordsForUserSql implements SqlStatement
{
    private string $sql;


    private function __construct()
    {
        $this->sql = '(SELECT '
                     . 'words.id, words.norsk, words.german, '
                     . 'wordsSuccessCounterToUsers.successCounter, wordsSuccessCounterToUsers.username '
                     . 'FROM words '
                     . 'LEFT JOIN wordsSuccessCounterToUsers ON words.id = wordsSuccessCounterToUsers.WordId '
                     . 'WHERE wordsSuccessCounterToUsers.username IS NULL AND words.active = 1) '
                     . 'UNION ALL'
                     . '(SELECT '
                     . 'words.id, words.norsk, words.german, '
                     . 'wordsSuccessCounterToUsers.successCounter, wordsSuccessCounterToUsers.username '
                     . 'FROM words '
                     . 'JOIN wordsSuccessCounterToUsers ON words.id = wordsSuccessCounterToUsers.WordId '
                     . 'WHERE wordsSuccessCounterToUsers.username = ?)'
                     . 'ORDER BY id DESC';
    }


    public static function create(): self
    {
        return new self();
    }


    public function asString(): string
    {
        return $this->sql;
    }
}
