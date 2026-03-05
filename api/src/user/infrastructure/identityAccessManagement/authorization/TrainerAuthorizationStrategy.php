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
use norsk\api\user\domain\port\UserReadingRepository;
use norsk\api\user\domain\valueObjects\UserName;

readonly class TrainerAuthorizationStrategy implements AuthorizationMiddlewareStrategy
{
    public function __construct(
        private UserReadingRepository $userRepository,
        private Url $url
    ) {
    }


    public function authorize(AuthenticatedUserInterface $authenticatedUser): AuthorizationResult
    {
        if ($this->isAllowedRole($authenticatedUser)) {
            return AuthorizationResult::granted(
                $authenticatedUser->getUserName(),
                $authenticatedUser->getRole()
            );
        }

        return AuthorizationResult::denied();
    }


    private function isAllowedRole(AuthenticatedUserInterface $authenticatedUser): bool
    {
        if ($authenticatedUser->roleEquals(Role::MANAGER)) {
            return true;
        }

        if ($authenticatedUser->roleEquals(Role::USER)) {
            return true;
        }

        return false;
    }


    public function checkActive(AuthenticatedUserInterface $authenticatedUser): void
    {
        $this->userRepository->checkIfUserExists($authenticatedUser->getUserName());
    }


    public function unauthorizedResponse(): Response
    {
        return UnauthorizedResponse::noTrainingRights($this->url);
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
            return LogMessage::fromString('Could not authenticate user for training: ' . $userName->asString());
        }

        return LogMessage::fromString('Could not authenticate user for training without user name.');
    }
}
