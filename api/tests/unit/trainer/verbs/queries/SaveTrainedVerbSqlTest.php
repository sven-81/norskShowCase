<?php

declare(strict_types=1);

namespace norsk\api\trainer\verbs\queries;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SaveTrainedVerbSql::class)]
class SaveTrainedVerbSqlTest extends TestCase
{
    private SaveTrainedVerbSql $saveTrainedWordSql;


    public function testCanBeUsedAsString(): void
    {
        self::assertSame(
            'INSERT INTO `verbsSuccessCounterToUsers` (`username`, `verbId`, `successCounter`, `timestamp`) '
            . 'SELECT ?, ?, 1, NOW() '
            . 'FROM `verbs` '
            . 'WHERE `id` = ? AND `active` = 1 '
            . 'ON DUPLICATE KEY UPDATE '
            . '`successCounter`=`successCounter`+1, `timestamp` = NOW();',
            $this->saveTrainedWordSql->asString()
        );
    }


    protected function setUp(): void
    {
        $this->saveTrainedWordSql = SaveTrainedVerbSql::create();
    }
}
