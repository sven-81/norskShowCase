<?php

declare(strict_types=1);

namespace norsk\api\app\response;

class ResponseHeaders
{
    private readonly array $headers;


    private function __construct(private readonly Url $url)
    {
        $this->headers = [
            'Access-Control-Allow-Origin' => $this->url->asString(),
            'Access-Control-Allow-Credentials' => true,
            'Access-Control-Allow-Methods' => 'POST, GET, DELETE, PUT, PATCH, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, Version',
            'Accept' => 'application/json',
            'Version' => 'HTTP/2',
            'Content-Type' => 'application/json',
            'Cache-Control' => true,
        ];
    }


    public static function create(Url $url): self
    {
        return new self($url);
    }


    public function asArray(): array
    {
        return $this->headers;
    }
}
