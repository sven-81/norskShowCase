<?php

declare(strict_types=1);

namespace norsk\api\manager\exceptions;

use norsk\api\app\request\Payload;
use norsk\api\app\response\ResponseCode;
use norsk\api\manager\Identifier;
use norsk\api\shared\VocabularyType;
use norsk\api\tests\provider\VocabularyTypeProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

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

        $object = new stdClass();
        $object->someKey = 'someValue';
        $object->someOtherKey = 'someOtherValue';

        $payload = Payload::by($object);

        $identifier = Identifier::fromPayload($payload);

        $exception = new GermanRecordAlreadyInDatabaseException($identifier, $vocabularyType);

        $this->assertSame(
            'German word already exists for \"{\"someKey\":\"someValue\",\"someOtherKey\":\"someOtherValue\"}\"',
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
