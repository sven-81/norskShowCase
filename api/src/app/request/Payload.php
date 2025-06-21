<?php

declare(strict_types=1);

namespace norsk\api\app\request;

use GuzzleHttp\Psr7\ServerRequest;
use InvalidArgumentException;
use norsk\api\app\response\ResponseCode;
use norsk\api\shared\Json;
use stdClass;

class Payload
{
    private function __construct(private readonly array $payload)
    {
    }


    public static function of(ServerRequest $request): self
    {
        $asArray = $request->getParsedBody();
        self::ensureRequestIsNotNull($asArray);

        return new self($asArray);
    }


    private static function ensureRequestIsNotNull(object|array|null $asArray): void
    {
        if ($asArray === null) {
            throw new InvalidArgumentException('No request body', ResponseCode::badRequest->value);
        }
    }


    public static function by(stdClass $class): self
    {
        $asArray = Json::encodeFromStdClass($class)->asDecodedJson();

        return new self($asArray);
    }


    public function asArray(): array
    {
        return $this->payload;
    }


    public function asJson(): Json
    {
        return Json::encodeFromArray($this->payload);
    }
}
