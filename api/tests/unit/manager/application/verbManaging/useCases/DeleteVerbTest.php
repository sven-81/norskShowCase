<?php

declare(strict_types=1);

namespace norsk\api\manager\application\verbManaging\useCases;

use norsk\api\shared\domain\Id;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DeleteVerb::class)]
class DeleteVerbTest extends TestCase
{
    public function testCanGetId(): void
    {
        $id = Id::by(123);
        $createVerb = DeleteVerb::createBy($id);

        self::assertEquals($id, $createVerb->getId());
    }
}
