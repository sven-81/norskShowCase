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

class RegisterExceptionMapper
{
    private const int DUPLICATE_KEY = 1062;


    public static function map(Throwable|Exception $throwable, Url $url): ResponseInterface
    {
        $code = $throwable->getCode();


        return match (true) {
            $code === ResponseCode::badRequest->value
            => self::parameterMissingResponse($url, $throwable),

            $code === ResponseCode::unprocessable->value
            => self::invalidPasswordResponse($url, $throwable),

            $code === self::DUPLICATE_KEY
            => self::userAlreadyExistsResponse($url, $throwable),

            default => ErrorResponse::serverError($url, $throwable),
        };
    }


    private static function parameterMissingResponse(
        Url $url,
        Throwable|Exception $throwable
    ): Response {
        return ErrorResponse::badRequest($url, $throwable);
    }


    private static function invalidPasswordResponse(
        Url $url,
        Throwable|Exception $throwable
    ): Response {
        return ErrorResponse::unprocessable($url, $throwable);
    }


    private static function userAlreadyExistsResponse(
        Url $url,
        Throwable|Exception $throwable
    ): Response {
        return ErrorResponse::conflict($url, $throwable);
    }
}
