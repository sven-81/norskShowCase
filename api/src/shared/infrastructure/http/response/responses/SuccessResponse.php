<?php

declare(strict_types=1);

namespace norsk\api\shared\infrastructure\http\response\responses;

use GuzzleHttp\Psr7\Response;
use norsk\api\shared\application\Json;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\shared\infrastructure\http\response\ResponseHeaders;
use norsk\api\shared\infrastructure\http\response\Url;

class SuccessResponse
{
    public static function loggedIn(Url $url, Json $body): Response
    {
        return self::create($url, $body);
    }


    public static function deletedRecord(Url $url, Json $body): Response
    {
        return self::create($url, $body);
    }


    public static function corsOptions(Url $url): Response
    {
        return self::create($url);
    }


    private static function create(Url $url, ?Json $body = null): Response
    {
        $validBody = $body?->asString();

        return new Response(
            ResponseCode::success->value,
            ResponseHeaders::create($url)->asArray(),
            $validBody
        );
    }
}
