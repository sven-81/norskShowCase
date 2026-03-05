<?php

declare(strict_types=1);

namespace norsk\api\shared\infrastructure\http\response;

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


    public static function noManagingRights(Url $url): Response
    {
        return new Response(
            ResponseCode::unauthorized->value,
            ResponseHeaders::create($url)->asArray(),
            body: '{"message":"Unauthorized: No rights for managing words or verbs"}'
        );
    }


    public static function noTrainingRights(Url $url): Response
    {
        return new Response(
            ResponseCode::unauthorized->value,
            ResponseHeaders::create($url)->asArray(),
            body: '{"message":"Unauthorized: No rights for training words or verbs"}'
        );
    }
}
