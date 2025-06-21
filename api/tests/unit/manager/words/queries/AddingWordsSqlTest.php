<?php

declare(strict_types=1);

namespace norsk\api\manager\words\queries;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AddingWordsSql::class)]
class AddingWordsSqlTest extends TestCase
{
    private AddingWordsSql $addingWordsSql;


    public function testCanBeUsedAsString(): void
    {
        self::assertSame(
            'INSERT INTO `words` (`german`, `norsk`) VALUES (?, ?);',
            $this->addingWordsSql->asString()
        );
    }


    protected function setUp(): void
    {
        $this->addingWordsSql = AddingWordsSql::create();
    }
}
