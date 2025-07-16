<?php

declare(strict_types=1);

namespace norsk\api\manager\domain\exceptions;

use norsk\api\manager\domain\Identifier;
use norsk\api\shared\domain\VocabularyType;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\tests\provider\VocabularyTypeProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(RecordAlreadyInDatabaseException::class)]
class RecordAlreadyInDatabaseExceptionTest extends TestCase
{
    private MockObject|Identifier $identifierMock;


    public static function getVocabularyType(): array
    {
        return VocabularyTypeProvider::getVocabularyType();
    }


    #[DataProvider('getVocabularyType')]
    public function testExceptionMessage(VocabularyType $vocabularyType): void
    {
        $exception = new RecordAlreadyInDatabaseException($this->identifierMock, $vocabularyType);

        $this->assertSame(
            ucfirst($vocabularyType->value) . ' already exists for testIdentifier',
            $exception->getMessage()
        );
    }


    #[DataProvider('getVocabularyType')]
    public function testExceptionCode(VocabularyType $vocabularyType): void
    {
        $exception = new RecordAlreadyInDatabaseException($this->identifierMock, $vocabularyType);

        $this->assertSame(ResponseCode::conflict->value, $exception->getCode());
    }


    protected function setUp(): void
    {
        $this->identifierMock = $this->createMock(Identifier::class);
        $this->identifierMock->method('asMessageString')
            ->willReturn('testIdentifier');
    }
}
