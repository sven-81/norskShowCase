<?php

declare(strict_types=1);

namespace norsk\api\app\identityAccessManagement;

use norsk\api\app\logging\Logger;
use norsk\api\app\logging\LogMessage;
use norsk\api\app\response\UnauthorizedResponse;
use norsk\api\app\response\Url;
use norsk\api\shared\responses\ErrorResponse;
use norsk\api\user\UserName;
use norsk\api\user\UsersReader;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Throwable;

class Authorization implements MiddlewareInterface
{
    public function __construct(
        private readonly Logger $logger,
        private readonly UsersReader $usersReader,
        private readonly Url $url
    ) {
    }


    public function process(Request $request, RequestHandler $handler): ResponseInterface
    {
        $userName = null;
        try {
            if (Session::isValidManager()) {
                $userName = Session::getUserName();
                $this->usersReader->isActiveManager($userName);

                $logMessage = LogMessage::fromString('Authenticated manager: ' . $userName->asString());
                $this->logger->info($logMessage);

                return $handler->handle($request);
            }
        } catch (Throwable $throwable) {
            $logMessage = $this->getLogMessage($userName);
            $this->logger->info(LogMessage::fromString($logMessage));
            $this->logger->error($throwable);

            return ErrorResponse::unauthorized($this->url, $throwable);
        }

        return UnauthorizedResponse::noRights($this->url);
    }


    private function getLogMessage(?UserName $userName): string
    {
        if ($userName instanceof UserName) {
            return 'Could not authenticate manager: ' . $userName->asString();
        }

        return 'Could not authenticate manager without user name.';
    }
}
