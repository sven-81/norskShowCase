<?php

declare(strict_types=1);

namespace norsk\api\manager\words;

use norsk\api\shared\German;
use norsk\api\shared\Id;
use norsk\api\shared\Json;
use norsk\api\shared\Norsk;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ManagedWord::class)]
class ManagedWordTest extends TestCase
{
    private Id $id;

    private German $german;

    private Norsk $norsk;

    private ManagedWord $managedWord;


    public function testCanGetId(): void
    {
        self::assertSame($this->id, $this->managedWord->getId());
    }


    public function testCanGetAsJson(): void
    {
        $expected = Json::encodeFromArray(
            [
                'id' => $this->id->asInt(),
                'german' => $this->german->asString(),
                'norsk' => $this->norsk->asString(),
            ]
        );
        self::assertEquals($expected, $this->managedWord->asJson());
    }


    protected function setUp(): void
    {
        $this->id = Id::by(1);
        $this->german = German::of('norwegisch');
        $this->norsk = Norsk::of('norsk');

        $this->managedWord = ManagedWord::of($this->id, $this->german, $this->norsk);
    }
}
