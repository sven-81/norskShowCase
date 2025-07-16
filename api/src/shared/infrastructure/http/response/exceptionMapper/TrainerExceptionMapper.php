<?php

declare(strict_types=1);

namespace norsk\api\shared\infrastructure\http\response\exceptionMapper;

use Exception;
use GuzzleHttp\Psr7\Response;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\shared\infrastructure\http\response\responses\ErrorResponse;
use norsk\api\shared\infrastructure\http\response\Url;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class TrainerExceptionMapper
{

    public static function map(Throwable|Exception $throwable, Url $url): ResponseInterface
    {
        $code = $throwable->getCode();

        return match (true) {
            $code === ResponseCode::badRequest->value
            => self::parameterMissingResponse($url, $throwable),

            $code === ResponseCode::notFound->value
            => self::vocabularyIdNotFoundResponse($url, $throwable),

            default => ErrorResponse::serverError($url, $throwable),
        };
    }


    private static function parameterMissingResponse(
        Url $url,
        Throwable|Exception $throwable
    ): Response {
        return ErrorResponse::badRequest($url, $throwable);
    }


    private static function vocabularyIdNotFoundResponse(
        Url $url,
        Throwable|Exception $throwable
    ): Response {
        return ErrorResponse::notFound($url, $throwable);
    }
}
