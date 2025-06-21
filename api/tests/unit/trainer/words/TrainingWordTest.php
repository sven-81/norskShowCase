<?php

declare(strict_types=1);

namespace norsk\api\trainer\words;

use norsk\api\shared\German;
use norsk\api\shared\Id;
use norsk\api\shared\Norsk;
use norsk\api\trainer\SuccessCounter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TrainingWord::class)]
class TrainingWordTest extends TestCase
{
    private Id $id;

    private TrainingWord $word;

    private SuccessCounter $successCounter;


    public function testCanGetId(): void
    {
        self::assertSame($this->id, $this->word->getId());
    }


    public function testCanGetSuccessCounter(): void
    {
        self::assertSame($this->successCounter, $this->word->getSuccessCounter());
    }


    public function testCanBeUsedAsJson(): void
    {
        self::assertSame(
            '{"id":3,"german":"Sch\u00e4renk\u00fcste","norsk":"skj\u00e6rg\u00e5rd"}',
            $this->word->asJson()->asString()
        );
    }


    protected function setUp(): void
    {
        $this->id = Id::by(3);
        $german = German::of('Schärenküste');
        $norsk = Norsk::of('skjærgård');
        $this->successCounter = SuccessCounter::by(3);

        $this->word = TrainingWord::of(
            $this->id,
            $german,
            $norsk,
            $this->successCounter,
        );
    }
}
