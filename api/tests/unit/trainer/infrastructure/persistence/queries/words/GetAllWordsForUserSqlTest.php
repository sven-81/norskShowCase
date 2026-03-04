<?php

declare(strict_types=1);

namespace norsk\api\trainer\infrastructure\persistence\queries\words;

use norsk\api\trainer\infrastructure\persistence\queries\words\GetAllWordsForUserSql;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GetAllWordsForUserSql::class)]
class GetAllWordsForUserSqlTest extends TestCase
{
    private GetAllWordsForUserSql $getAllWordsForUserSql;


    public function testCanBeUsedAsString(): void
    {
        self::assertSame(
            '(SELECT '
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
            . 'ORDER BY id DESC',
            $this->getAllWordsForUserSql->asString()
        );
    }


    protected function setUp(): void
    {
        $this->getAllWordsForUserSql = GetAllWordsForUserSql::create();
    }
}
