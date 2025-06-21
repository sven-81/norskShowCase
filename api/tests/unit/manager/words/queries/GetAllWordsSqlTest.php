<?php

declare(strict_types=1);

namespace norsk\api\manager\words\queries;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GetAllWordsSql::class)]
class GetAllWordsSqlTest extends TestCase
{
    private GetAllWordsSql $getAllWordsSql;


    public function testCanBeUsedAsString(): void
    {
        self::assertSame(
            'SELECT `id`, `german`, `norsk` FROM `words` WHERE `active`=1 ORDER BY `id` ASC;',
            $this->getAllWordsSql->asString()
        );
    }


    protected function setUp(): void
    {
        $this->getAllWordsSql = GetAllWordsSql::create();
    }
}
