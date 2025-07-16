<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\persistence\queries;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FindUserSql::class)]
class FindUserSqlTest extends TestCase
{
    private FindUserSql $findUserSql;


    public function testCanBeUsedAsString(): void
    {
        self::assertSame(
            'SELECT `username` FROM `users` WHERE `username` = ? AND `active` = 1;',
            $this->findUserSql->asString()
        );
    }


    protected function setUp(): void
    {
        $this->findUserSql = FindUserSql::create();
    }
}
