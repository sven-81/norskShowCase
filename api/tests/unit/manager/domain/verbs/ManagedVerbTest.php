<?php

declare(strict_types=1);

namespace norsk\api\manager\domain\verbs;

use LogicException;
use norsk\api\shared\domain\Id;
use norsk\api\shared\domain\VocabularyPersistencePort;
use norsk\api\tests\provider\VerbProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(ManagedVerb::class)]
class ManagedVerbTest extends TestCase
{
    private Id $id;

    private ManagedVerb $verb;

    private ManagedVerb $managedVerb;

    private ManagedVerb $newVerb;

    private VocabularyPersistencePort|MockObject $writerMock;


    protected function setUp(): void
    {
        $this->id = Id::by(3);
        $this->verb = VerbProvider::managedVerbToGo();

        $this->managedVerb = ManagedVerb::fromPersistence(
            $this->id,
            $this->verb->getGerman(),
            $this->verb->getNorsk(),
            $this->verb->getNorskPresent(),
            $this->verb->getNorskPast(),
            $this->verb->getNorskPastPerfect()
        );

        $this->newVerb = ManagedVerb::createNew(
            $this->verb->getGerman(),
            $this->verb->getNorsk(),
            $this->verb->getNorskPresent(),
            $this->verb->getNorskPast(),
            $this->verb->getNorskPastPerfect()
        );

        $this->writerMock = $this->createMock(VocabularyPersistencePort::class);
    }


    public function testCanCreateFromPersisted(): void
    {
        /** @phpstan-ignore-next-line */
        self::assertNotNull($this->managedVerb->getId());
    }


    public function testCanGetId(): void
    {
        self::assertSame($this->id, $this->managedVerb->getId());
    }


    public function testCanGetGerman(): void
    {
        $this->assertSame($this->verb->getGerman(), $this->managedVerb->getGerman());
    }


    public function testCanGetNorsk(): void
    {
        $this->assertSame($this->verb->getNorsk(), $this->managedVerb->getNorsk());
    }


    public function testCanGetNorskPresent(): void
    {
        $this->assertSame($this->verb->getNorskPresent(), $this->managedVerb->getNorskPresent());
    }


    public function testCanGetNorskPast(): void
    {
        $this->assertSame($this->verb->getNorskPast(), $this->managedVerb->getNorskPast());
    }


    public function testCanGetNorskPastPerfect(): void
    {
        $this->assertSame($this->verb->getNorskPastPerfect(), $this->managedVerb->getNorskPastPerfect());
    }


    public function testPersistWithVocabPersistencePort(): void
    {
        $this->writerMock->expects($this->once())
            ->method('saveNewVerb')
            ->with($this->newVerb);

        $this->newVerb->persistWith($this->writerMock);
    }


    public function testUpdateWithVocabPersistencePort(): void
    {
        $this->writerMock->expects($this->once())
            ->method('saveEditedVerb')
            ->with($this->verb);

        $this->verb->updateWith($this->writerMock);
    }


    public function testThrowsExceptionIfGetIdWouldNotBeDefined(): void
    {
        $this->expectExceptionObject(
            new LogicException('Cannot access Id of a non-persisted ManagedVerb.')
        );
        self::assertSame($this->id, $this->newVerb->getId());
    }


    public function testCanGetAsJson(): void
    {
        self::assertEquals(
            '{"id":3,"german":"gehen","norsk":"g\u00e5","norskPresent":"g\u00e5r",'
            . '"norskPast":"gikk","norskPastPerfect":"har g\u00e5tt"}',
            $this->managedVerb->asJson()->asString()
        );
    }
}
