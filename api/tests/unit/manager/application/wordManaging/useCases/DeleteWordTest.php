<?php

declare(strict_types=1);

namespace norsk\api\manager\application\wordManaging\useCases;

use norsk\api\shared\domain\Id;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DeleteWord::class)]
class DeleteWordTest extends TestCase
{
    public function testCanGetId(): void
    {
        $id = Id::by(123);
        $createWord = DeleteWord::createBy($id);

        self::assertEquals($id, $createWord->getId());
    }
}
