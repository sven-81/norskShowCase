<?php

declare(strict_types=1);

namespace norsk\api\app\identityAccessManagement;

use norsk\api\app\response\UnauthorizedResponse;
use norsk\api\app\response\Url;
use norsk\api\shared\responses\ErrorResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Throwable;

class Authentication implements MiddlewareInterface
{
    private const string AUTHORIZATION = 'Authorization';


    public function __construct(
        private readonly JwtManagement $jwtManagement,
        private readonly Session $session,
        private readonly Url $url,
    ) {
    }


    public function process(Request $request, RequestHandler $handler): ResponseInterface
    {
        try {
            if ($this->AuthorizationHeaderDoesNotExist($request)) {
                return UnauthorizedResponse::noHeader($this->url);
            }

            $this->jwtManagement->validate($request, $this->session);
        } catch (Throwable $throwable) {
            return ErrorResponse::unauthorized($this->url, $throwable);
        }

        return $handler->handle($request);
    }


    private function AuthorizationHeaderDoesNotExist(Request $request): bool
    {
        return count($request->getHeader(self::AUTHORIZATION)) < 1;
    }
}
