<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\identityAccessManagement\authorization;

use norsk\api\infrastructure\logging\Logger;
use norsk\api\shared\infrastructure\http\response\responses\ErrorResponse;
use norsk\api\shared\infrastructure\http\response\Url;
use norsk\api\user\domain\model\AuthorizationResult;
use norsk\api\user\domain\model\JwtAuthenticatedUser;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Throwable;

readonly class Authorization implements MiddlewareInterface
{
    public function __construct(
        private Logger                          $logger,
        private AuthorizationMiddlewareStrategy $strategy,
        private Url                             $url
    )
    {
    }


    public function process(Request $request, RequestHandler $handler): ResponseInterface
    {
        $result = AuthorizationResult::denied();

        try {
            $authenticatedUser = JwtAuthenticatedUser::byRequest($request);
            $result = $this->strategy->authorize($authenticatedUser);

            if ($result->wasDenied()) {
                return $this->strategy->unauthorizedResponse();
            }

            $this->strategy->checkActive($authenticatedUser);
            $this->logger->info($this->strategy->successLogging($result));

            return $handler->handle($request);
        } catch (Throwable $throwable) {
            $this->logger->info($this->strategy->infoLogMessageForError($result->getUserName()));
            $this->logger->error($throwable);

            return ErrorResponse::unauthorized($this->url, $throwable);
        }
    }
}
