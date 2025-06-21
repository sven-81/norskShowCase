<?php

declare(strict_types=1);

namespace norsk\api\trainer\words\queries;

use norsk\api\app\persistence\SqlStatement;

class SaveTrainedWordSql implements SqlStatement
{
    private readonly string $sql;


    private function __construct()
    {
        $this->sql = 'INSERT INTO `wordsSuccessCounterToUsers` (`username`, `wordId`, `successCounter`, `timestamp`) '
                     . 'SELECT ?, ?, 1, NOW() '
                     . 'FROM `words` '
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
