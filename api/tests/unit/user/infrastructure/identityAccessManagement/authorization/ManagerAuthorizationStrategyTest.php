<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\identityAccessManagement\authorization;

use InvalidArgumentException;
use norsk\api\infrastructure\logging\LogMessage;
use norsk\api\shared\infrastructure\http\response\UnauthorizedResponse;
use norsk\api\shared\infrastructure\http\response\Url;
use norsk\api\user\application\AuthenticatedUserInterface;
use norsk\api\user\domain\model\AuthorizationResult;
use norsk\api\user\domain\model\Role;
use norsk\api\user\domain\port\ManagerAuthorizationRepository;
use norsk\api\user\domain\valueObjects\UserName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ManagerAuthorizationStrategy::class)]
class ManagerAuthorizationStrategyTest extends TestCase
{
    private ManagerAuthorizationStrategy $managerAuthorization;

    private UserName $userName;

    private Url $url;


    protected function setUp(): void
    {
        $this->userName = UserName::by('someUserName');
        $this->url = Url::by('http://foo');

        $this->managerAuthorization = new ManagerAuthorizationStrategy(
            $this->createStub(ManagerAuthorizationRepository::class),
            $this->url
        );
    }


    public function testAuthorizeReturnsDeniedIfRoleIsNotManager(): void
    {
        self::assertEquals(
            AuthorizationResult::denied(),
            $this->managerAuthorization->authorize($this->createStub(AuthenticatedUserInterface::class))
        );
    }


    public function testAuthorizeReturnsGrantedForManagerRole(): void
    {
        $role = Role::MANAGER;
        $authenticatedUserStub = $this->createStub(AuthenticatedUserInterface::class);

        $authenticatedUserStub
            ->method('roleEquals')
            ->willReturnCallback(fn($passedRole): bool => $passedRole === $role);
        $authenticatedUserStub->method('getUserName')->willReturn($this->userName);
        $authenticatedUserStub->method('getRole')->willReturn($role);

        self::assertEquals(
            AuthorizationResult::granted($this->userName, $role),
            $this->managerAuthorization->authorize($authenticatedUserStub)
        );
    }


    public function testCanCheckActive(): void
    {
        $repoMock = $this->createMock(ManagerAuthorizationRepository::class);
        $authenticatedUserMock = $this->createMock(AuthenticatedUserInterface::class);

        $authenticatedUserMock->expects($this->once())
            ->method('getUserName')
            ->willReturn($this->userName);

        $repoMock->expects($this->once())
            ->method('isActiveManager')
            ->with($this->userName);

        $strategy = new ManagerAuthorizationStrategy($repoMock, $this->url);
        $strategy->checkActive($authenticatedUserMock);
    }


    public function testCanCreateUnauthorizedResponse(): void
    {
        self::assertEquals(
            UnauthorizedResponse::noManagingRights($this->url)->getStatusCode(),
            $this->managerAuthorization->unauthorizedResponse()->getStatusCode()
        );
    }


    public function testCanGetLogMessageForSuccess(): void
    {
        $result = AuthorizationResult::granted($this->userName, Role::MANAGER);

        self::assertEquals(
            LogMessage::fromString('Authorized manager: someUserName'),
            $this->managerAuthorization->successLogging($result)
        );
    }


    public function testSuccessLoggingThrowsExceptionIfUserNameIsNull(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('UserName is not defined.'));

        $this->managerAuthorization->successLogging(AuthorizationResult::denied());
    }


    public function testCanGetLogMessageIfUserNameExists(): void
    {
        self::assertEquals(
            LogMessage::fromString('Could not authenticate manager: ' . $this->userName->asString()),
            $this->managerAuthorization->infoLogMessageForError($this->userName)
        );
    }


    public function testCanGetLogMessageIfUserNameDoesNotExist(): void
    {
        self::assertEquals(
            LogMessage::fromString('Could not authenticate manager without user name.'),
            $this->managerAuthorization->infoLogMessageForError(null)
        );
    }
}
