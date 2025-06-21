<?php

declare(strict_types=1);

namespace norsk\api\manager\verbs\queries;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GetAllVerbsSql::class)]
class GetAlVerbsSqlTest extends TestCase
{
    private GetAllVerbsSql $getAllVerbsSql;


    public function testCanBeUsedAsString(): void
    {
        self::assertSame(
            'SELECT `id`, `german`, `norsk`, `norsk_present`, `norsk_past`, `norsk_past_perfekt` '
            . 'FROM `verbs` WHERE `active`=1 ORDER BY `id` ASC;',
            $this->getAllVerbsSql->asString()
        );
    }


    protected function setUp(): void
    {
        $this->getAllVerbsSql = GetAllVerbsSql::create();
    }
}
