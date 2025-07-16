<?php

declare(strict_types=1);

namespace norsk\api\manager\application\wordManaging\useCases;

use norsk\api\manager\domain\words\ManagedWord;
use norsk\api\shared\domain\Id;
use norsk\api\shared\infrastructure\http\request\Payload;
use norsk\api\tests\provider\WordProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(UpdateWord::class)]
class UpdateWordTest extends TestCase
{

    private Id $id;

    private Payload|MockObject $payloadMock;

    private UpdateWord $command;

    private ManagedWord $word;


    protected function setUp(): void
    {
        $this->word = WordProvider::managedWordArchipelago();
        $this->payloadMock = $this->createMock(Payload::class);
        $this->payloadMock->method('asArray')
            ->willReturn($this->word->asJson()->asDecodedJson());

        $this->id = Id::by(123);
        $this->command = UpdateWord::createBy($this->id, $this->payloadMock);
    }


    public function testCanGetId(): void
    {
        $this->assertEquals($this->id, $this->command->getId());
    }


    public function testCanGetGerman(): void
    {
        $this->assertEquals($this->word->getGerman(), $this->command->getGerman());
    }


    public function testCanGetNorsk(): void
    {
        $this->assertEquals($this->word->getNorsk(), $this->command->getNorsk());
    }
}
