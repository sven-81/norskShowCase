<?php

declare(strict_types=1);

namespace norsk\api\user\queries;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FindUserDataSql::class)]
class FindUserDataSqlTest extends TestCase
{
    private FindUserDataSql $findUserDataSql;


    public function testCanBeUsedAsString(): void
    {
        self::assertSame(
            'SELECT `username`, `firstname`, `lastname`, `password_hash`, `salt`, `role`, `active` '
            . 'FROM `users` WHERE `username` = ?;',
            $this->findUserDataSql->asString()
        );
    }


    protected function setUp(): void
    {
        $this->findUserDataSql = FindUserDataSql::create();
    }
}
