<?php

declare(strict_types=1);

namespace norsk\api\app\response;

use GuzzleHttp\Psr7\Response;

class UnauthorizedResponse
{
    public static function noHeader(Url $url): Response
    {
        return new Response(
            ResponseCode::unauthorized->value,
            ResponseHeaders::create($url)->asArray(),
            body: '{"message":"No Authorization header sent"}'
        );
    }


    public static function noRights(Url $url): Response
    {
        return new Response(
            ResponseCode::unauthorized->value,
            ResponseHeaders::create($url)->asArray(),
            body: '{"message":"Unauthorized: No rights for managing words or verbs"}'
        );
    }
}
