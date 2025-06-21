<?php

declare(strict_types=1);

namespace norsk\api\trainer\verbs;

use norsk\api\shared\German;
use norsk\api\shared\Id;
use norsk\api\shared\Norsk;
use norsk\api\trainer\SuccessCounter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TrainingVerb::class)]
class TrainingVerbTest extends TestCase
{
    private Id $id;

    private TrainingVerb $verb;

    private SuccessCounter $successCounter;


    public function testCanGetId(): void
    {
        self::assertSame($this->id, $this->verb->getId());
    }


    public function testCanGetSuccessCounter(): void
    {
        self::assertSame($this->successCounter, $this->verb->getSuccessCounter());
    }


    public function testCanBeUsedAsJson(): void
    {
        self::assertSame(
            '{"id":3,"german":"gehen","norsk":"g\u00e5","norskPresent":"g\u00e5r",'
            . '"norskPast":"gikk","norskPastPerfect":"har g\u00e5tt"}',
            $this->verb->asJson()->asString()
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
        $this->successCounter = SuccessCounter::by(3);

        $this->verb = TrainingVerb::of(
            $this->id,
            $german,
            $norsk,
            $norskPresent,
            $norskPast,
            $norskPastPerfect,
            $this->successCounter,
        );
    }
}
