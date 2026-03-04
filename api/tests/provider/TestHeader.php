<?php

declare(strict_types=1);

namespace norsk\api\tests\provider;

use norsk\api\shared\infrastructure\http\response\Url;

trait TestHeader
{
    public function getTestHeaderAsHeaders(Url $url): array
    {
        return [
            'Access-Control-Allow-Origin' => [
                $url->asString(),
            ],
            'Access-Control-Allow-Credentials' => ['true'],
            'Access-Control-Allow-Methods' => ['POST, GET, DELETE, PUT, PATCH, OPTIONS'],
            'Access-Control-Allow-Headers' => ['Content-Type, Authorization, X-Requested-With, Version'],
            'Accept' => ['application/json'],
            'Version' => ['HTTP/2'],
            'Content-Type' => ['application/json'],
            'Cache-Control' => [true],
        ];
    }


    public function getTestHeaderAsArray(Url $url): array
    {
        return [
            'Access-Control-Allow-Origin' => $url->asString(),
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Methods' => 'POST, GET, DELETE, PUT, PATCH, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, Version',
            'Accept' => 'application/json',
            'Version' => 'HTTP/2',
            'Content-Type' => 'application/json',
            'Cache-Control' => true,
        ];
    }
}
