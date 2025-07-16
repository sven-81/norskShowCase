<?php

declare(strict_types=1);

namespace norsk\api\trainer\infrastructure\persistence\queries\verbs;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GetAllVerbsForUserSql::class)]
class GetAllVerbsForUserSqlTest extends TestCase
{
    private GetAllVerbsForUserSql $getAllWordsForUserSql;


    public function testCanBeUsedAsString(): void
    {
        self::assertSame(
            '(SELECT '
            . 'verbs.id, verbs.norsk, verbs.norsk_present, verbs.norsk_past, verbs.norsk_past_perfekt, '
            . 'verbs.german, verbsSuccessCounterToUsers.successCounter, verbsSuccessCounterToUsers.username '
            . 'FROM verbs '
            . 'LEFT JOIN verbsSuccessCounterToUsers ON verbs.id = verbsSuccessCounterToUsers.verbId '
            . 'WHERE verbsSuccessCounterToUsers.username IS NULL AND verbs.active = 1) '
            . 'UNION ALL'
            . '(SELECT '
            . 'verbs.id, verbs.norsk, verbs.norsk_present, verbs.norsk_past, verbs.norsk_past_perfekt, '
            . 'verbs.german, verbsSuccessCounterToUsers.successCounter, verbsSuccessCounterToUsers.username '
            . 'FROM verbs '
            . 'JOIN verbsSuccessCounterToUsers ON verbs.id = verbsSuccessCounterToUsers.verbId '
            . 'WHERE verbsSuccessCounterToUsers.username = ?)'
            . 'ORDER BY id DESC',
            $this->getAllWordsForUserSql->asString()
        );
    }


    protected function setUp(): void
    {
        $this->getAllWordsForUserSql = GetAllVerbsForUserSql::create();
    }
}
