<?php

declare(strict_types=1);

namespace norsk\api\trainer\infrastructure\persistence\queries\verbs;

use norsk\api\infrastructure\persistence\SqlStatement;

readonly class SaveTrainedVerbSql implements SqlStatement
{
    private string $sql;


    private function __construct()
    {
        $this->sql = 'INSERT INTO `verbsSuccessCounterToUsers` (`username`, `verbId`, `successCounter`, `timestamp`) '
                     . 'SELECT ?, ?, 1, NOW() '
                     . 'FROM `verbs` '
                     . 'WHERE `id` = ? AND `active` = 1 '
                     . 'ON DUPLICATE KEY UPDATE '
                     . '`successCounter`=`successCounter`+1, `timestamp` = NOW();';
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
