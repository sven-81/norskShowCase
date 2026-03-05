<?php

declare(strict_types=1);

namespace norsk\api\manager\infrastructure\persistence\queries\words;

use norsk\api\manager\infrastructure\persistence\queries\verbs\AddingVerbsSql;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AddingVerbsSql::class)]
class AddingVerbsSqlTest extends TestCase
{
    private AddingVerbsSql $addingVerbsSql;


    public function testCanBeUsedAsString(): void
    {
        self::assertSame(
            'INSERT INTO `verbs` (`german`, `norsk`, `norsk_present`, '
            . '`norsk_past`, `norsk_past_perfekt`) VALUES (?, ?, ?, ?, ?);',
            $this->addingVerbsSql->asString()
        );
    }


    protected function setUp(): void
    {
        $this->addingVerbsSql = AddingVerbsSql::create();
    }
}
