<?php

declare(strict_types=1);

namespace norsk\api\trainer\verbs\queries;

use norsk\api\app\persistence\SqlStatement;

class SaveTrainedVerbSql implements SqlStatement
{
    private readonly string $sql;


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
