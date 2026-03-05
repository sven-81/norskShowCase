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

class ManagerExceptionMapper
{
    public static function mapForCreate(Throwable|Exception $throwable, Url $url): ResponseInterface
    {
        $code = $throwable->getCode();

        return match (true) {
            $code === ResponseCode::conflict->value
            => self::entryAlreadyExistsResponse($url, $throwable),

            default => ErrorResponse::serverError($url, $throwable),
        };
    }


    public static function mapForUpdate(Throwable|Exception $throwable, Url $url): ResponseInterface
    {
        $code = $throwable->getCode();

        return match (true) {
            $code === ResponseCode::conflict->value
            => self::entryAlreadyExistsResponse($url, $throwable),

            $code === ResponseCode::notFound->value
            => self::noVocabularyFoundForRequestedId($url, $throwable),

            default => ErrorResponse::serverError($url, $throwable),
        };
    }


    public static function mapForDelete(Throwable|Exception $throwable, Url $url): ResponseInterface
    {
        $code = $throwable->getCode();

        return match (true) {
            $code === ResponseCode::notFound->value
            => self::noVocabularyFoundForRequestedId($url, $throwable),

            default => ErrorResponse::serverError($url, $throwable),
        };
    }


    private static function entryAlreadyExistsResponse(
        Url $url,
        Throwable|Exception $throwable
    ): Response {
        return ErrorResponse::conflict($url, $throwable);
    }


    private static function noVocabularyFoundForRequestedId(
        Url $url,
        Throwable|Exception $throwable
    ): Response {
        return ErrorResponse::notFound($url, $throwable);
    }
}
