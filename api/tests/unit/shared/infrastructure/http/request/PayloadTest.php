<?php

declare(strict_types=1);

namespace norsk\api\shared\infrastructure\http\request;

use GuzzleHttp\Psr7\ServerRequest;
use InvalidArgumentException;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(Payload::class)]
class PayloadTest extends TestCase
{
    /** @var string[] */
    private array $expectedArray;

    private ServerRequest $requestStub;


    protected function setUp(): void
    {
        $this->expectedArray = [
            'someKey' => 'someValue',
            'someOtherKey' => 'someOtherValue',
        ];

        $this->requestStub = $this->createStub(ServerRequest::class);
        $this->requestStub->method('getParsedBody')
            ->willReturn($this->expectedArray);
    }


    public function testCanBeUsedOfRequestAsArray(): void
    {
        self::assertEquals($this->expectedArray, Payload::of($this->requestStub)->asArray());
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
            Payload::of($this->requestStub)->asJson()->asString()
        );
    }


    public function testThrowsExceptionIfResponseIsNull(): void
    {
        $this->expectExceptionObject(
            new InvalidArgumentException('No request body', ResponseCode::badRequest->value)
        );

        $requestStub = $this->createStub(ServerRequest::class);
        $requestStub->method('getParsedBody')
            ->willReturn(null);

        Payload::of($requestStub);
    }
}
