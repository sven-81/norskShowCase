<?php

declare(strict_types=1);

namespace norsk\api\trainer\infrastructure\persistence\queries\verbs;

use norsk\api\infrastructure\persistence\SqlStatement;

readonly class GetAllVerbsForUserSql implements SqlStatement
{
    private string $sql;


    private function __construct()
    {
        $this->sql = '(SELECT '
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
