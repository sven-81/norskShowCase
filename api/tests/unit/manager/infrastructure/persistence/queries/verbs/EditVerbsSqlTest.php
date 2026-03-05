<?php

declare(strict_types=1);

namespace norsk\api\manager\infrastructure\persistence\queries\words;

use norsk\api\manager\infrastructure\persistence\queries\verbs\EditVerbsSql;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EditVerbsSql::class)]
class EditVerbsSqlTest extends TestCase
{
    private EditVerbsSql $editVerbsSql;


    public function testCanBeUsedAsString(): void
    {
        self::assertSame(
            'UPDATE `verbs` SET `german`=?, `norsk`=? , '
            . '`norsk_present`=?, `norsk_past`=?, `norsk_past_perfekt`=? '
            . 'WHERE `id`=? AND `active`=1;',
            $this->editVerbsSql->asString()
        );
    }


    protected function setUp(): void
    {
        $this->editVerbsSql = EditVerbsSql::create();
    }
}
