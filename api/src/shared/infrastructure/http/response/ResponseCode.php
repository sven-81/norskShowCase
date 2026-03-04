<?php

declare(strict_types=1);

namespace norsk\api\shared\infrastructure\http\response;

enum ResponseCode: int
{
    case success = 200;
    case created = 201;
    case noContent = 204;
    case badRequest = 400;
    case unauthorized = 401;
    case forbidden = 403;
    case notFound = 404;
    case conflict = 409;
    case unprocessable = 422;
    case serverError = 500;
}
