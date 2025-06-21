<?php

declare(strict_types=1);

namespace norsk\api\trainer\responses;

use GuzzleHttp\Psr7\Response;
use norsk\api\app\response\ResponseCode;
use norsk\api\app\response\ResponseHeaders;
use norsk\api\app\response\Url;
use norsk\api\shared\Json;

class VocabularyToTrainResponse
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
