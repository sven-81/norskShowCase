<?php

declare(strict_types=1);

namespace norsk\api\manager\domain\words;

use LogicException;
use norsk\api\shared\application\Json;
use norsk\api\shared\domain\Id;
use norsk\api\shared\domain\VocabularyPersistencePort;
use norsk\api\tests\provider\WordProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(ManagedWord::class)]
class ManagedWordTest extends TestCase
{
    private Id $id;

    private ManagedWord $managedWord;

    private ManagedWord $word;

    private ManagedWord $newWord;

    private VocabularyPersistencePort|MockObject $writerMock;


    protected function setUp(): void
    {
        $this->id = Id::by(1);
        $this->word = WordProvider::managedWordArchipelago();

        $this->managedWord = ManagedWord::fromPersistence(
            $this->id,
            $this->word->getGerman(),
            $this->word->getNorsk()
        );

        $this->newWord = ManagedWord::createNew(
            $this->word->getGerman(),
            $this->word->getNorsk()
        );

        $this->writerMock = $this->createMock(VocabularyPersistencePort::class);
    }


    public function testCanCreateFromPersisted(): void
    {
        /** @phpstan-ignore-next-line */
        self::assertNotNull($this->managedWord->getId());
    }


    public function testCanGetId(): void
    {
        self::assertSame($this->id, $this->managedWord->getId());
    }


    public function testCanGetGerman(): void
    {
        $this->assertSame($this->word->getGerman(), $this->managedWord->getGerman());
    }


    public function testCanGetNorsk(): void
    {
        $this->assertSame($this->word->getNorsk(), $this->managedWord->getNorsk());
    }


    public function testPersistWithVocabPersistencePort(): void
    {
        $this->writerMock->expects($this->once())
            ->method('saveNewWord')
            ->with($this->newWord);

        $this->newWord->persistWith($this->writerMock);
    }


    public function testUpdateWithVocabPersistencePort(): void
    {
        $this->writerMock->expects($this->once())
            ->method('saveEditedWord')
            ->with($this->word);

        $this->word->updateWith($this->writerMock);
    }


    public function testThrowsExceptionIfGetIdWouldNotBeDefined(): void
    {
        $this->expectExceptionObject(
            new LogicException('Cannot access Id of a non-persisted ManagedWord.')
        );
        self::assertSame($this->id, $this->newWord->getId());
    }


    public function testCanGetAsJson(): void
    {
        $expected = Json::encodeFromArray(
            [
                'id' => $this->id->asInt(),
                'german' => $this->word->getGerman()->asString(),
                'norsk' => $this->word->getNorsk()->asString(),
            ]
        );
        self::assertEquals($expected, $this->managedWord->asJson());
    }
}
