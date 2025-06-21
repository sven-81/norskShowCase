<?php

declare(strict_types=1);

namespace norsk\api\shared\responses;

use GuzzleHttp\Psr7\Response;
use norsk\api\app\response\ResponseCode;
use norsk\api\app\response\ResponseHeaders;
use norsk\api\app\response\Url;

class ConflictResponse
{
    public static function create(Url $url): Response
    {
        $responseBody = '{}';

        return new Response(
            ResponseCode::conflict->value,
            ResponseHeaders::create($url)->asArray(),
            $responseBody
        );
    }
}
