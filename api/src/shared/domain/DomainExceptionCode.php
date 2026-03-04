<?php

declare(strict_types=1);

namespace norsk\api\shared\domain;

enum DomainExceptionCode: int
{
    case invalidInput = 422;
    case notFound = 404;
    case forbidden = 403;
    case unauthorized = 401;
    case conflict = 409;
    case internalError = 500;
    case badRequest = 400;
}

