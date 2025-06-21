<?php

declare(strict_types=1);

namespace norsk\api\app\persistence;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(TableName::class)]
class TableNameTest extends TestCase
{
    public static function getTableNames(): array
    {
        return [
            'users' => ['users', TableName::users->value],
            'words' => ['words', TableName::words->value],
            'verbs' => ['verbs', TableName::verbs->value],
            'verbsSuccessCounterToUsers' => [
                'verbsSuccessCounterToUsers',
                TableName::verbsSuccessCounterToUsers->value,
            ],
            'wordsSuccessCounterToUsers' => [
                'wordsSuccessCounterToUsers',
                TableName::wordsSuccessCounterToUsers->value,
            ],
        ];
    }


    #[DataProvider('getTableNames')]
    public function testEnsureTableNames($expected, $givenName): void
    {
        self::assertEquals($expected, $givenName);
    }
}
