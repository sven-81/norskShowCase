<?php

declare(strict_types=1);

namespace norsk\api\shared\responses;

use GuzzleHttp\Psr7\Response;
use norsk\api\app\response\ResponseCode;
use norsk\api\app\response\ResponseHeaders;
use norsk\api\app\response\Url;

class NoContentResponse
{
    public static function vocabularyTrainedSuccessfully(Url $url): Response
    {
        return self::create($url);
    }


    private static function create(Url $url): Response
    {
        return new Response(
            ResponseCode::noContent->value,
            ResponseHeaders::create($url)->asArray(),
            null
        );
    }


    public static function updatedVocabularySuccessfully(Url $url): Response
    {
        return self::create($url);
    }
}
