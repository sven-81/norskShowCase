<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\identityAccessManagement\authentication;

use norsk\api\shared\infrastructure\http\response\responses\ErrorResponse;
use norsk\api\shared\infrastructure\http\response\UnauthorizedResponse;
use norsk\api\shared\infrastructure\http\response\Url;
use norsk\api\user\domain\model\JwtAuthenticatedUser;
use norsk\api\user\infrastructure\identityAccessManagement\jwt\JwtManagement;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Throwable;

class Authentication implements MiddlewareInterface
{
    private const string AUTHORIZATION = 'Authorization';
    private const string AUTHENTICATED_USER = 'authenticatedUser';


    public function __construct(
        private readonly JwtManagement $jwtManagement,
        private readonly Url $url,
    ) {
    }


    public function process(Request $request, RequestHandler $handler): ResponseInterface
    {
        try {
            if ($this->AuthorizationHeaderDoesNotExist($request)) {
                return UnauthorizedResponse::noHeader($this->url);
            }

            $decodedPayload = $this->jwtManagement->validate($request);
            $authenticatedUser = JwtAuthenticatedUser::byPayload($decodedPayload);

            $request = $request->withAttribute(self::AUTHENTICATED_USER, $authenticatedUser);
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
