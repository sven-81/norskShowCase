<?php

declare(strict_types=1);

namespace norsk\api\manager\application\wordManaging\useCases;

use norsk\api\manager\domain\words\ManagedWord;
use norsk\api\shared\infrastructure\http\request\Payload;
use norsk\api\tests\provider\WordProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(CreateWord::class)]
class CreateWordTest extends TestCase
{

    private CreateWord $createWord;

    private ManagedWord $word;


    protected function setUp(): void
    {
        $this->word = WordProvider::managedWordArchipelago();
        $payloadMock = $this->createMock(Payload::class);
        $payloadMock->method('asArray')
            ->willReturn($this->word->asJson()->asDecodedJson());

        $this->createWord = CreateWord::createBy($payloadMock);
    }


    public function testCanGetGerman(): void
    {
        self::assertEquals($this->word->getGerman(), $this->createWord->getGerman());
    }


    public function testCanGetNorsk(): void
    {
        self::assertEquals($this->word->getNorsk(), $this->createWord->getNorsk());
    }
}
