<?php

declare(strict_types=1);

namespace norsk\api\user\domain\service;

use norsk\api\shared\infrastructure\http\request\Payload;
use norsk\api\user\domain\model\ValidatedUser;
use norsk\api\user\infrastructure\identityAccessManagement\jwt\JsonWebToken;
use Psr\Http\Message\ServerRequestInterface;

interface JwtService
{
    public function create(ValidatedUser $validatedUser): JsonWebToken;


    public function validate(ServerRequestInterface $request): Payload;
}
