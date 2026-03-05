<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\identityAccessManagement\authorization;

use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use norsk\api\infrastructure\logging\LogMessage;
use norsk\api\shared\infrastructure\http\response\UnauthorizedResponse;
use norsk\api\shared\infrastructure\http\response\Url;
use norsk\api\user\application\AuthenticatedUserInterface;
use norsk\api\user\domain\model\AuthorizationResult;
use norsk\api\user\domain\model\Role;
use norsk\api\user\domain\port\ManagerAuthorizationRepository;
use norsk\api\user\domain\valueObjects\UserName;

readonly class ManagerAuthorizationStrategy implements AuthorizationMiddlewareStrategy
{
    public function __construct(
        private ManagerAuthorizationRepository $authorizationRepository,
        private Url $url
    ) {
    }


    public function authorize(AuthenticatedUserInterface $authenticatedUser): AuthorizationResult
    {
        if ($authenticatedUser->roleEquals(Role::MANAGER)) {
            return AuthorizationResult::granted(
                $authenticatedUser->getUserName(),
                $authenticatedUser->getRole()
            );
        }

        return AuthorizationResult::denied();
    }


    public function checkActive(AuthenticatedUserInterface $authenticatedUser): void
    {
        $this->authorizationRepository->isActiveManager($authenticatedUser->getUserName());
    }


    public function unauthorizedResponse(): Response
    {
        return UnauthorizedResponse::noManagingRights($this->url);
    }


    public function successLogging(AuthorizationResult $result): LogMessage
    {
        $userName = $this->ensureUserNameIsDefined($result);

        return LogMessage::fromString(sprintf('Authorized %s: %s', $result->getRole()->value, $userName->asString()));
    }


    private function ensureUserNameIsDefined(AuthorizationResult $result): UserName
    {
        if ($result->getUserName() === null) {
            throw new InvalidArgumentException('UserName is not defined.');
        }

        return $result->getUserName();
    }


    public function infoLogMessageForError(?UserName $userName): LogMessage
    {
        if ($userName instanceof UserName) {
            return LogMessage::fromString('Could not authenticate manager: ' . $userName->asString());
        }

        return LogMessage::fromString('Could not authenticate manager without user name.');
    }
}
