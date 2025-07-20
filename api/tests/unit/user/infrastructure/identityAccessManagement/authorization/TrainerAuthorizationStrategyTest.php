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

#[CoversClass(TrainerAuthorizationStrategy::class)]
class TrainerAuthorizationStrategyTest extends TestCase
{
    private UsersReader|MockObject $usersReaderMock;

    private TrainerAuthorizationStrategy $trainerAuthorization;

    private UserName $userName;

    private AuthenticatedUserInterface|MockObject $authenticatedUserMock;

    private Url $url;


    protected function setUp(): void
    {
        $this->usersReaderMock = $this->createMock(UsersReader::class);

        $this->userName = UserName::by('someUserName');
        $this->authenticatedUserMock = $this->createMock(AuthenticatedUserInterface::class);
        $this->url = Url::by('http://foo');

        $this->trainerAuthorization = new TrainerAuthorizationStrategy(
            $this->usersReaderMock,
            $this->url
        );
    }


    public static function getRole(): array
    {
        return [
            'manager' => [Role::MANAGER],
            'user' => [Role::USER],
        ];
    }


    public function testAutorizeReturnsEmptyDecisionIf(): void
    {
        self::assertEquals(
            AuthorizationDecision::by(),
            $this->trainerAuthorization->authorize($this->authenticatedUserMock)
        );
    }


    #[DataProvider('getRole')]
    public function testAutorizeReturnsValidDecision(Role $role): void
    {
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
            AuthorizationDecision::by(true, $this->userName, $role),
            $this->trainerAuthorization->authorize($this->authenticatedUserMock)
        );
    }


    public function testCanCheckActive(): void
    {
        $this->authenticatedUserMock
            ->expects($this->once())
            ->method('getUserName')
            ->willReturn($this->userName);

        $this->usersReaderMock->expects($this->once())
            ->method('checkIfUserExists')
            ->with($this->userName);

        $this->trainerAuthorization->checkActive($this->authenticatedUserMock);
    }


    public function testCanCreateUnauthorizedResponse(): void
    {
        self::assertEquals(
            UnauthorizedResponse::noTrainingRights($this->url)->getStatusCode(),
            $this->trainerAuthorization->unauthorizedResponse()->getStatusCode()
        );
    }


    public function testCanGetLogMessageForSuccess(): void
    {
        self::assertEquals(
            LogMessage::fromString("Authorized manager: someUserName"),
            $this->trainerAuthorization->successLogging(
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

        $this->trainerAuthorization->successLogging(
            AuthorizationDecision::by(
                isAuthorized: true,
                role: Role::MANAGER
            )
        );
    }


    public function testCanGetLogMassageIfUserNameExists(): void
    {
        self::assertEquals(
            LogMessage::fromString('Could not authenticate user for training: ' . $this->userName->asString()),
            $this->trainerAuthorization->infoLogMessageForError($this->userName)
        );
    }


    public function testCanGetLogMassageIfUserNameDoesNotExist(): void
    {
        self::assertEquals(
            LogMessage::fromString('Could not authenticate user for training without user name.'),
            $this->trainerAuthorization->infoLogMessageForError(null)
        );
    }
}
