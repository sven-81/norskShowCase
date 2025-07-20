<?php

declare(strict_types=1);

namespace norsk\api\shared\infrastructure\http\response\responses;

use GuzzleHttp\Psr7\Response;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\shared\infrastructure\http\response\ResponseHeaders;
use norsk\api\shared\infrastructure\http\response\Url;

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
