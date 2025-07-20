<?php

declare(strict_types=1);

namespace norsk\api\manager\infrastructure\persistence\queries\words;

use norsk\api\manager\infrastructure\persistence\queries\words\EditWordsSql;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EditWordsSql::class)]
class EditWordsSqlTest extends TestCase
{
    private EditWordsSql $editWordsSql;


    public function testCanBeUsedAsString(): void
    {
        self::assertSame(
            'UPDATE `words` SET `german`=?, `norsk`=? WHERE `id`=? AND `active`=1;',
            $this->editWordsSql->asString()
        );
    }


    protected function setUp(): void
    {
        $this->editWordsSql = EditWordsSql::create();
    }
}
