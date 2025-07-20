<?php

declare(strict_types=1);

namespace norsk\api\shared\application;

use norsk\api\shared\domain\exceptions\InvalidJsonArgumentException;
use norsk\api\shared\infrastructure\http\request\Payload;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(Json::class)]
class JsonTest extends TestCase
{
    public function testCanBeUsedFromArrayAsString(): void
    {
        $this->assertSame('{"key":"value"}', Json::encodeFromArray(['key' => 'value'])->asString());
    }


    public function testCanBeUsedFromStringAsString(): void
    {
        $this->assertSame('{"key":"value"}', Json::fromString('{"key":"value"}')->asString());
    }


    public function testCanBeUsedFromStdClassAsString(): void
    {
        $object = new stdClass();
        $object->key = 'value';

        $this->assertSame('{"key":"value"}', Json::encodeFromStdClass($object)->asString());
    }


    public function testThrowsExceptionIfInvalidStringIsGivenToEncode(): void
    {
        $this->expectExceptionObject(
            new InvalidJsonArgumentException(
                'Cannot create json from: nix'
            )
        );

        Json::fromString('nix');
    }


    public function testThrowsExceptionIfArrayIsInvalidForEncoding(): void
    {
        $this->expectExceptionObject(
            new InvalidJsonArgumentException(
                'Could not encode to json from array'
            )
        );

        $invalid = mb_convert_encoding('äöü', 'ISO-8859-1', 'UTF-8');
        $data = [$invalid];
        Json::encodeFromArray($data)->asDecodedJson();
    }


    public function testCanGetEscapedJsonInJsonString(): void
    {
        $object = new stdClass();
        $object->someKey = [
            'eins' => 1,
            '2' => 'zwei',
        ];

        $json = Json::asEscaped(Payload::by($object));
        $this->assertSame('"{\"someKey\":{\"eins\":1,\"2\":\"zwei\"}}"', $json->asString());
    }
}
