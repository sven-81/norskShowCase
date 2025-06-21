<?php

declare(strict_types=1);

namespace norsk\api\manager\verbs;

use norsk\api\shared\German;
use norsk\api\shared\Id;
use norsk\api\shared\Norsk;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ManagedVerb::class)]
class ManagedVerbTest extends TestCase
{
    private Id $id;

    private ManagedVerb $managedVerb;


    public function testCanGetId(): void
    {
        self::assertSame($this->id, $this->managedVerb->getId());
    }


    public function testCanGetAsJson(): void
    {
        self::assertEquals(
            '{"id":3,"german":"gehen","norsk":"g\u00e5","norskPresent":"g\u00e5r",'
            . '"norskPast":"gikk","norskPastPerfect":"har g\u00e5tt"}',
            $this->managedVerb->asJson()->asString()
        );
    }


    protected function setUp(): void
    {
        $this->id = Id::by(3);
        $german = German::of('gehen');
        $norsk = Norsk::of('gå');
        $norskPresent = Norsk::of('går');
        $norskPast = Norsk::of('gikk');
        $norskPastPerfect = Norsk::of('har gått');

        $this->managedVerb = ManagedVerb::of(
            $this->id,
            $german,
            $norsk,
            $norskPresent,
            $norskPast,
            $norskPastPerfect
        );
    }
}
