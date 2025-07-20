<?php

declare(strict_types=1);

namespace norsk\api\manager\application\verbManaging\useCases;

use norsk\api\manager\domain\verbs\ManagedVerb;
use norsk\api\shared\infrastructure\http\request\Payload;
use norsk\api\tests\provider\VerbProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CreateVerb::class)]
class CreateVerbTest extends TestCase
{

    private CreateVerb $createVerb;

    private ManagedVerb $verb;


    protected function setUp(): void
    {
        $this->verb = VerbProvider::managedVerbToGo();

        $payloadMock = $this->createMock(Payload::class);
        $payloadMock->method('asArray')
            ->willReturn($this->verb->asJson()->asDecodedJson());

        $this->createVerb = CreateVerb::createBy($payloadMock);
    }


    public function testCanGetGerman(): void
    {
        self::assertEquals($this->verb->getGerman(), $this->createVerb->getGerman());
    }


    public function testCanGetNorsk(): void
    {
        self::assertEquals($this->verb->getNorsk(), $this->createVerb->getNorsk());
    }


    public function testCanGetNorskPresent(): void
    {
        self::assertEquals($this->verb->getNorskPresent(), $this->createVerb->getNorskPresent());
    }


    public function testCanGetNorskPast(): void
    {
        self::assertEquals($this->verb->getNorskPast(), $this->createVerb->getNorskPast());
    }


    public function testCanGetNorskPastPerfect(): void
    {
        self::assertEquals($this->verb->getNorskPastPerfect(), $this->createVerb->getNorskPastPerfect());
    }
}
