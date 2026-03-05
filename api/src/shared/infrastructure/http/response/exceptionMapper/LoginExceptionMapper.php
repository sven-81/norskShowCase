<?php

declare(strict_types=1);

namespace norsk\api\shared\infrastructure\http\response\exceptionMapper;

use Exception;
use GuzzleHttp\Psr7\Response;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\shared\infrastructure\http\response\responses\ErrorResponse;
use norsk\api\shared\infrastructure\http\response\Url;
use norsk\api\user\domain\exceptions\ParameterMissingException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class LoginExceptionMapper
{
    public static function map(Throwable|Exception $throwable, Url $url): ResponseInterface
    {
        $code = $throwable->getCode();

        return match (true) {
            $throwable instanceof ParameterMissingException, $code === ResponseCode::badRequest->value
            => self::parameterMissingResponse($url, $throwable),

            $code === ResponseCode::unauthorized->value
            => self::credentialsAreInvalidOrUserDoesNotExistResponse($url, $throwable),

            $code === ResponseCode::forbidden->value
            => self::noActiveUserResponse($url, $throwable),

            $code === ResponseCode::unprocessable->value
            => self::inputNotValidResponse($url, $throwable),

            default => ErrorResponse::serverError($url, $throwable),
        };
    }


    private static function credentialsAreInvalidOrUserDoesNotExistResponse(
        Url $url,
        Throwable|Exception $throwable
    ): Response {
        return ErrorResponse::unauthorized($url, $throwable);
    }


    private static function noActiveUserResponse(
        Url $url,
        Throwable|Exception $throwable
    ): Response {
        return ErrorResponse::forbidden($url, $throwable);
    }


    private static function parameterMissingResponse(
        Url $url,
        Throwable|Exception $throwable
    ): Response {
        return ErrorResponse::badRequest($url, $throwable);
    }


    private static function inputNotValidResponse(
        Url $url,
        Throwable|Exception $throwable
    ): Response {
        return ErrorResponse::unprocessable($url, $throwable);
    }
}
