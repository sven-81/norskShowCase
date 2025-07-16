<?php

declare(strict_types=1);

namespace norsk\api\shared\infrastructure\http\response\responses;

use Exception;
use GuzzleHttp\Psr7\Response;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\shared\infrastructure\http\response\ResponseHeaders;
use norsk\api\shared\infrastructure\http\response\Url;
use Throwable;

class ErrorResponse
{
    public static function badRequest(Url $url, Throwable|Exception $throwable): Response
    {
        return self::create($url, $throwable, ResponseCode::badRequest);
    }


    private static function create(Url $url, Throwable|Exception $throwable, ResponseCode $responseCode): Response
    {
        $responseBody = '{"message":"' . $throwable->getMessage() . '"}';

        return new Response(
            $responseCode->value,
            ResponseHeaders::create($url)->asArray(),
            $responseBody
        );
    }


    public static function unauthorized(Url $url, Throwable|Exception $throwable): Response
    {
        return self::create($url, $throwable, ResponseCode::unauthorized);
    }


    public static function forbidden(Url $url, Throwable|Exception $throwable): Response
    {
        return self::create($url, $throwable, ResponseCode::forbidden);
    }


    public static function notFound(Url $url, Throwable|Exception $throwable): Response
    {
        return self::create($url, $throwable, ResponseCode::notFound);
    }


    public static function unprocessable(Url $url, Throwable|Exception $throwable): Response
    {
        return self::create($url, $throwable, ResponseCode::unprocessable);
    }


    public static function serverError(Url $url, Throwable|Exception $throwable): Response
    {
        return self::create($url, $throwable, ResponseCode::serverError);
    }


    public static function conflict(Url $url, Throwable|Exception $throwable): Response
    {
        return self::create($url, $throwable, ResponseCode::conflict);
    }
}
