<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\identityAccessManagement\authorization;

use norsk\api\infrastructure\logging\Logger;
use norsk\api\shared\infrastructure\http\response\responses\ErrorResponse;
use norsk\api\shared\infrastructure\http\response\Url;
use norsk\api\user\domain\model\JwtAuthenticatedUser;
use norsk\api\user\domain\service\AuthorizationStrategy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Throwable;

class Authorization implements MiddlewareInterface
{
    public function __construct(
        private readonly Logger $logger,
        private readonly AuthorizationStrategy $strategy,
        private readonly Url $url
    ) {
    }


    public function process(Request $request, RequestHandler $handler): ResponseInterface
    {
        $authorizedDecision = AuthorizationDecision::by();

        try {
            $authenticatedUser = JwtAuthenticatedUser::byRequest($request);
            $authorizedDecision = $this->strategy->authorize($authenticatedUser);

            if ($authorizedDecision->failed()) {
                return $this->strategy->unauthorizedResponse();
            }

            $this->strategy->checkActive($authenticatedUser);
            $logMessage = $this->strategy->successLogging($authorizedDecision);
            $this->logger->info(
                $logMessage
            );

            return $handler->handle($request);
        } catch (Throwable $throwable) {
            $this->logger->info(
                $this->strategy->infoLogMessageForError($authorizedDecision->getUserName())
            );
            $this->logger->error($throwable);

            return ErrorResponse::unauthorized($this->url, $throwable);
        }
    }
}
