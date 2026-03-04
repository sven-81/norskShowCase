<?php

declare(strict_types=1);

namespace norsk\api\manager\domain;

use norsk\api\shared\domain\German;
use norsk\api\shared\domain\Id;
use norsk\api\shared\domain\Norsk;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Identifier::class)]
class IdentifierTest extends TestCase
{
    public function testCanBeUsedAsStringFromId(): void
    {
        self::assertSame(
            'id: 3',
            Identifier::fromId(Id::by(3))->asMessageString()
        );
    }


    public function testCanBeUsedAsStringFromVocabulary(): void
    {
        $german = German::of('identifier');
        $norsk = Norsk::of('identifikator');

        self::assertSame(
            'identifier | identifikator',
            Identifier::fromVocabulary($german, $norsk)->asMessageString()
        );
    }
}
