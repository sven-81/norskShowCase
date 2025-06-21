<?php

declare(strict_types=1);

namespace norsk\api\shared\responses;

use GuzzleHttp\Psr7\Response;
use norsk\api\app\response\ResponseCode;
use norsk\api\app\response\ResponseHeaders;
use norsk\api\app\response\Url;

class CreatedResponse
{
    public static function savedNewUser(Url $url): Response
    {
        return self::created($url);
    }


    public static function savedVocabulary(Url $url): Response
    {
        return self::created($url);
    }


    private static function created(Url $url): Response
    {
        $responseBody = '{}';

        return new Response(
            ResponseCode::created->value,
            ResponseHeaders::create($url)->asArray(),
            $responseBody
        );
    }
}
