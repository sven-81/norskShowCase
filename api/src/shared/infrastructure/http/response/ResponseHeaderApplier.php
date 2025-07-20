<?php

declare(strict_types=1);

namespace norsk\api\shared\infrastructure\http\response;

use Psr\Http\Message\ResponseInterface;

class ResponseHeaderApplier
{
    private function __construct(private readonly ResponseHeaders $headers)
    {
    }


    public static function fromHeaders(ResponseHeaders $headers): self
    {
        return new self($headers);
    }


    public function apply(ResponseInterface $response): ResponseInterface
    {
        foreach ($this->headers->asArray() as $key => $value) {
            $response = $response->withHeader($key, (string)$value);
        }

        return $response;
    }
}
