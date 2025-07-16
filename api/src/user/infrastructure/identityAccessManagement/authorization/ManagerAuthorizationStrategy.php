<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\identityAccessManagement\authorization;

use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use norsk\api\infrastructure\logging\LogMessage;
use norsk\api\shared\infrastructure\http\response\UnauthorizedResponse;
use norsk\api\shared\infrastructure\http\response\Url;
use norsk\api\user\application\AuthenticatedUserInterface;
use norsk\api\user\domain\model\Role;
use norsk\api\user\domain\service\AuthorizationStrategy;
use norsk\api\user\domain\valueObjects\UserName;
use norsk\api\user\infrastructure\persistence\UsersReader;

class ManagerAuthorizationStrategy implements AuthorizationStrategy
{

    public function __construct(
        private readonly UsersReader $usersReader,
        private readonly Url $url
    ) {
    }


    public function authorize(AuthenticatedUserInterface $authenticatedUser): AuthorizationDecision
    {
        $managerRole = $authenticatedUser->roleEquals(Role::MANAGER);
        if ($managerRole) {
            return AuthorizationDecision::by(
                isAuthorized: true,
                userName: $authenticatedUser->getUsername(),
                role: $authenticatedUser->getRole()
            );
        }

        return AuthorizationDecision::by();
    }


    public function checkActive(AuthenticatedUserInterface $authenticatedUser): void
    {
        $this->usersReader->isActiveManager($authenticatedUser->getUserName());
    }


    public function unauthorizedResponse(): Response
    {
        return UnauthorizedResponse::noManagingRights($this->url);
    }


    public function successLogging(AuthorizationDecision $authorizationDecision): LogMessage
    {
        $userName = $this->getUserName($authorizationDecision);

        return LogMessage::fromString(
            sprintf(
                "Authorized %s: %s",
                $authorizationDecision->getRole()->value,
                $userName->asString()
            )
        );
    }


    private function getUserName(AuthorizationDecision $authorizationDecision): UserName
    {
        if ($authorizationDecision->getUserName() === null) {
            throw new InvalidArgumentException('UserName is not defined.');
        }

        return $authorizationDecision->getUserName();
    }


    public function infoLogMessageForError(?UserName $userName): LogMessage
    {
        return LogMessage::fromString($this->createLogMessage($userName));
    }


    private function createLogMessage(?UserName $userName): string
    {
        if ($userName instanceof UserName) {
            return 'Could not authenticate manager: ' . $userName->asString();
        }

        return 'Could not authenticate manager without user name.';
    }
}