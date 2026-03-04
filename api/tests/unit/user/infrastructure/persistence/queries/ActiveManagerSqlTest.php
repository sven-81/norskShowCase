<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\persistence\queries;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ActiveManagerSql::class)]
class ActiveManagerSqlTest extends TestCase
{
    private ActiveManagerSql $activeManagerSql;


    public function testCanBeUsedAsString(): void
    {
        self::assertSame(
            'SELECT `username` FROM `users` WHERE `username` = ? AND `active` = 1 AND `role` = "manager";',
            $this->activeManagerSql->asString()
        );
    }


    protected function setUp(): void
    {
        $this->activeManagerSql = ActiveManagerSql::create();
    }
}
