<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\persistence\queries;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AddUserSql::class)]
class AddUserSqlTest extends TestCase
{
    private AddUserSql $addingUserSql;


    public function testCanBeUsedAsString(): void
    {
        self::assertSame(
            'INSERT INTO `users` (`username`, `firstname`, `lastname`, `password_hash`, `salt`, `active`) '
            . 'VALUES (?, ?, ?, ?, ?, 0);',
            $this->addingUserSql->asString()
        );
    }


    protected function setUp(): void
    {
        $this->addingUserSql = AddUserSql::create();
    }
}
