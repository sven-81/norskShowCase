<?php

declare(strict_types=1);

namespace norsk\api\shared\infrastructure\http\request;

use GuzzleHttp\Psr7\ServerRequest;
use InvalidArgumentException;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(Payload::class)]
class PayloadTest extends TestCase
{
    /** @var string[] */
    private array $expectedArray;

    private MockObject|ServerRequest $requestMock;


    protected function setUp(): void
    {
        $this->expectedArray = [
            'someKey' => 'someValue',
            'someOtherKey' => 'someOtherValue',
        ];

        $this->requestMock = $this->createMock(ServerRequest::class);
        $this->requestMock->method('getParsedBody')
            ->willReturn($this->expectedArray);
    }


    public function testCanBeUsedOfRequestAsArray(): void
    {
        self::assertEquals($this->expectedArray, Payload::of($this->requestMock)->asArray());
    }


    public function testCanBeUsedByStdClassAsArray(): void
    {
        $object = new stdClass();
        $object->someKey = 'someValue';
        $object->someOtherKey = 'someOtherValue';

        self::assertEquals($this->expectedArray, Payload::by($object)->asArray());
    }


    public function testCanBeUsedAsJson(): void
    {
        $expectedJson = '{"someKey":"someValue","someOtherKey":"someOtherValue"}';
        self::assertJsonStringEqualsJsonString(
            $expectedJson,
            Payload::of($this->requestMock)->asJson()->asString()
        );
    }


    public function testThrowsExceptionIfResponseIsNull(): void
    {
        $this->expectExceptionObject(
            new InvalidArgumentException('No request body', ResponseCode::badRequest->value)
        );

        $requestMock = $this->createMock(ServerRequest::class);
        $requestMock->method('getParsedBody')
            ->willReturn(null);

        Payload::of($requestMock);
    }
}
