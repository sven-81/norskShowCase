<?php

declare(strict_types=1);

namespace norsk\api\manager\infrastructure\web\responses;

use GuzzleHttp\Psr7\Response;
use norsk\api\shared\application\Json;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\shared\infrastructure\http\response\ResponseHeaders;
use norsk\api\shared\infrastructure\http\response\Url;

class VocabularyListResponse
{
    public static function create(Url $url, Json $body): Response
    {
        return new Response(
            ResponseCode::success->value,
            ResponseHeaders::create($url)->asArray(),
            $body->asString()
        );
    }
}
