<?php

declare(strict_types=1);

namespace norsk\api\manager\application\verbManaging\useCases;

use norsk\api\manager\domain\verbs\ManagedVerb;
use norsk\api\shared\domain\Id;
use norsk\api\shared\infrastructure\http\request\Payload;
use norsk\api\tests\provider\VerbProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UpdateVerb::class)]
class UpdateVerbTest extends TestCase
{

    private Id $id;

    private UpdateVerb $command;

    private ManagedVerb $verb;


    protected function setUp(): void
    {
        $this->verb = VerbProvider::managedVerbToGo();
        $payloadMock = $this->createMock(Payload::class);
        $payloadMock->method('asArray')
            ->willReturn($this->verb->asJson()->asDecodedJson());

        $this->id = Id::by(123);
        $this->command = UpdateVerb::createBy($this->id, $payloadMock);
    }


    public function testCanGetId(): void
    {
        $this->assertEquals($this->id, $this->command->getId());
    }


    public function testCanGetGerman(): void
    {
        $this->assertEquals($this->verb->getGerman(), $this->command->getGerman());
    }


    public function testCanGetNorsk(): void
    {
        $this->assertEquals($this->verb->getNorsk(), $this->command->getNorsk());
    }


    public function testCanGetNorskPresent(): void
    {
        $this->assertEquals($this->verb->getNorskPresent(), $this->command->getNorskPresent());
    }


    public function testCanGetNorskPast(): void
    {
        $this->assertEquals($this->verb->getNorskPast(), $this->command->getNorskPast());
    }


    public function testCanGetNorskPastPerfect(): void
    {
        $this->assertEquals($this->verb->getNorskPastPerfect(), $this->command->getNorskPastPerfect());
    }
}
