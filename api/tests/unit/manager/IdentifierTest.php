<?php

declare(strict_types=1);

namespace norsk\api\manager;

use norsk\api\app\request\Payload;
use norsk\api\shared\Id;
use norsk\api\shared\Json;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Identifier::class)]
class IdentifierTest extends TestCase
{
    public function testCanBeUsedAsStringFromId(): void
    {
        self::assertSame(
            'id: 3',
            Identifier::fromId(Id::by(3))->asMessageString()
        );
    }


    public function testCanBeUsedAsStringFromPayload(): void
    {
        $errorJson = Json::encodeFromArray(
            [
                'eins' => 1,
                2 => 'zwei',
            ]
        );

        $payloadMock = $this->createMock(Payload::class);
        $payloadMock->expects($this->once())
            ->method('asJson')
            ->willReturn($errorJson);

        self::assertSame(
            '\"{\"eins\":1,\"2\":\"zwei\"}\"',
            Identifier::fromPayload($payloadMock)->asMessageString()
        );
    }
}
