<?php

declare(strict_types=1);

namespace norsk\api\shared\application;

use InvalidArgumentException;
use JsonException;
use norsk\api\shared\domain\exceptions\InvalidJsonArgumentException;
use norsk\api\shared\infrastructure\http\request\Payload;
use stdClass;

class Json
{
    private const int DEPTH = 512;


    private function __construct(private readonly string $content)
    {
    }


    public static function fromString(string $content): self
    {
        if (!json_validate($content)) {
            throw new InvalidJsonArgumentException('Cannot create json from: ' . $content);
        }

        return new self($content);
    }


    public static function encodeFromArray(array $array): self
    {
        try {
            $encoded = json_encode($array, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new InvalidJsonArgumentException('Could not encode to json from array');
        }

        // @codeCoverageIgnoreStart
        if (!is_string($encoded)) {
            throw new InvalidJsonArgumentException('Unexpected non-string result from json_encode for array');
        }
        // @codeCoverageIgnoreEnd

        return new self($encoded);
    }


    public static function encodeFromStdClass(stdClass $class): self
    {
        try {
            $encoded = json_encode($class, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new InvalidJsonArgumentException('Could not encode to json from stdClass');
        }

        // @codeCoverageIgnoreStart
        if (!is_string($encoded)) {
            throw new InvalidJsonArgumentException('Unexpected non-string result from json_encode for stdClass');
        }
        // @codeCoverageIgnoreEnd

        return new self($encoded);
    }


    public static function asEscaped(Payload $payload): self
    {
        try {
            $innerJson = $payload->asJson()->asString();
            $outerJson = json_encode($innerJson, JSON_THROW_ON_ERROR);
        // @codeCoverageIgnoreStart
        } catch (JsonException) {
            throw new InvalidArgumentException('Could not encode to escaped json from payload array');
        }
        // @codeCoverageIgnoreEnd

        // @codeCoverageIgnoreStart
        if (!is_string($outerJson)) {
            throw new InvalidArgumentException('Unexpected non-string result from json_encode for escaped payload');
        }
        // @codeCoverageIgnoreEnd

        return new self($outerJson);
    }


    public function asString(): string
    {
        return $this->content;
    }


    public function asDecodedJson(): array
    {
        try {
            return json_decode($this->content, true, self::DEPTH, JSON_THROW_ON_ERROR);
            // @codeCoverageIgnoreStart
        } catch (JsonException) {
            throw new InvalidJsonArgumentException(
                'The given content is invalid and could not be decoded from json: ' . $this->content
            );
            // @codeCoverageIgnoreEnd
        }
    }
}
