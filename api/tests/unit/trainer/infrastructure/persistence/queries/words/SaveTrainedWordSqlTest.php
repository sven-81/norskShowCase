<?php

declare(strict_types=1);

namespace norsk\api\trainer\infrastructure\persistence\queries\words;

use norsk\api\trainer\infrastructure\persistence\queries\words\SaveTrainedWordSql;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SaveTrainedWordSql::class)]
class SaveTrainedWordSqlTest extends TestCase
{
    private SaveTrainedWordSql $saveTrainedWordSql;


    public function testCanBeUsedAsString(): void
    {
        self::assertSame(
            'INSERT INTO `wordsSuccessCounterToUsers` '
            . '(`username`, `wordId`, `successCounter`, `timestamp`) '
            . 'SELECT ?, ?, 1, NOW() FROM `words` '
            . 'WHERE `id` = ? AND `active` = 1 '
            . 'ON DUPLICATE KEY UPDATE `successCounter`=`successCounter`+1, `timestamp` = NOW();',
            $this->saveTrainedWordSql->asString()
        );
    }


    protected function setUp(): void
    {
        $this->saveTrainedWordSql = SaveTrainedWordSql::create();
    }
}
