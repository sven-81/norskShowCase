<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\identityAccessManagement\authorization;

use InvalidArgumentException;
use norsk\api\infrastructure\logging\LogMessage;
use norsk\api\shared\infrastructure\http\response\UnauthorizedResponse;
use norsk\api\shared\infrastructure\http\response\Url;
use norsk\api\user\application\AuthenticatedUserInterface;
use norsk\api\user\domain\model\Role;
use norsk\api\user\domain\valueObjects\UserName;
use norsk\api\user\infrastructure\persistence\UsersReader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(ManagerAuthorizationStrategy::class)]
class ManagerAuthorizationStrategyTest extends TestCase
{
    private UsersReader|MockObject $usersReaderMock;

    private ManagerAuthorizationStrategy $managerAuthorization;

    private UserName $userName;

    private AuthenticatedUserInterface|MockObject $authenticatedUserMock;

    private Url $url;


    protected function setUp(): void
    {
        $this->usersReaderMock = $this->createMock(UsersReader::class);

        $this->userName = UserName::by('someUserName');
        $this->authenticatedUserMock = $this->createMock(AuthenticatedUserInterface::class);
        $this->url = Url::by('http://foo');

        $this->managerAuthorization = new ManagerAuthorizationStrategy(
            $this->usersReaderMock,
            $this->url
        );
    }

    public function testAutorizeReturnsEmptyDecisionIf(): void
    {
        self::assertEquals(
            AuthorizationDecision::by(),
            $this->managerAuthorization->authorize($this->authenticatedUserMock)
        );
    }


    public function testAutorizeReturnsValidDecision(): void
    {
        $role = Role::MANAGER;

        $this->authenticatedUserMock
            ->method('roleEquals')
            ->willReturnCallback(fn($passedRole): bool => $passedRole === $role);
        $this->authenticatedUserMock
            ->expects($this->once())
            ->method('getUserName')
            ->willReturn($this->userName);
        $this->authenticatedUserMock
            ->expects($this->once())
            ->method('getRole')
            ->willReturn($role);

        self::assertEquals(
            AuthorizationDecision::by(isAuthorized: true, userName: $this->userName, role: $role),
            $this->managerAuthorization->authorize($this->authenticatedUserMock)
        );
    }


    public function testCanCheckActive(): void
    {
        $this->authenticatedUserMock
            ->expects($this->once())
            ->method('getUserName')
            ->willReturn($this->userName);

        $this->usersReaderMock->expects($this->once())
            ->method('isActiveManager')
            ->with($this->userName);

        $this->managerAuthorization->checkActive($this->authenticatedUserMock);
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
        self::assertEquals(
            LogMessage::fromString("Authorized manager: someUserName"),
            $this->managerAuthorization->successLogging(
                AuthorizationDecision::by(
                    isAuthorized: true,
                    userName: $this->userName,
                    role: Role::MANAGER
                )
            )
        );
    }


    public function testThrowsExceptionIfUserNameIsUnknownForSuccessLogging(): void
    {
        $this->expectExceptionObject(
            new InvalidArgumentException('UserName is not defined.')
        );

        $this->managerAuthorization->successLogging(
            AuthorizationDecision::by(
                isAuthorized: true,
                role: Role::MANAGER
            )
        );
    }


    public function testCanGetLogMassageIfUserNameExists(): void
    {
        self::assertEquals(
            LogMessage::fromString('Could not authenticate manager: ' . $this->userName->asString()),
            $this->managerAuthorization->infoLogMessageForError($this->userName)
        );
    }


    public function testCanGetLogMassageIfUserNameDoesNotExist(): void
    {
        self::assertEquals(
            LogMessage::fromString('Could not authenticate manager without user name.'),
            $this->managerAuthorization->infoLogMessageForError(null)
        );
    }
}
