<?php

declare(strict_types=1);

namespace norsk\api\manager\domain\exceptions;

use norsk\api\manager\domain\Identifier;
use norsk\api\shared\domain\German;
use norsk\api\shared\domain\Norsk;
use norsk\api\shared\domain\VocabularyType;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\tests\provider\VocabularyTypeProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(GermanRecordAlreadyInDatabaseException::class)]
class GermanRecordAlreadyInDatabaseExceptionTest extends TestCase
{
    private MockObject|Identifier $identifierMock;


    protected function setUp(): void
    {
        $this->identifierMock = $this->createMock(Identifier::class);
        $this->identifierMock->method('asMessageString')
            ->willReturn('testIdentifier');
    }


    #[DataProvider('getVocabularyType')]
    public function testExceptionMessage(VocabularyType $vocabularyType): void
    {
        $exception = new GermanRecordAlreadyInDatabaseException($this->identifierMock, $vocabularyType);

        $this->assertSame(
            'German ' . $vocabularyType->value . ' already exists for testIdentifier',
            $exception->getMessage()
        );
    }


    public function testException(): void
    {
        $vocabularyType = VocabularyType::word;

        $german = German::of('testen');
        $norsk = Norsk::of('tester');

        $identifier = Identifier::fromVocabulary($german, $norsk);

        $exception = new GermanRecordAlreadyInDatabaseException($identifier, $vocabularyType);

        $this->assertSame(
            'German word already exists for testen | tester',
            $exception->getMessage()
        );
    }


    public static function getVocabularyType(): array
    {
        return VocabularyTypeProvider::getVocabularyType();
    }


    #[DataProvider('getVocabularyType')]
    public function testExceptionCode(VocabularyType $vocabularyType): void
    {
        $exception = new GermanRecordAlreadyInDatabaseException($this->identifierMock, $vocabularyType);

        $this->assertSame(ResponseCode::conflict->value, $exception->getCode());
    }
}
