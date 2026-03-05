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
use norsk\api\user\domain\port\UserReadingRepository;
use norsk\api\user\domain\valueObjects\UserName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(TrainerAuthorizationStrategy::class)]
class TrainerAuthorizationStrategyTest extends TestCase
{
    private TrainerAuthorizationStrategy $trainerAuthorization;

    private UserName $userName;

    private Url $url;


    protected function setUp(): void
    {
        $this->userName = UserName::by('someUserName');
        $this->url = Url::by('http://foo');

        $this->trainerAuthorization = new TrainerAuthorizationStrategy(
            $this->createStub(UserReadingRepository::class),
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


    public function testAuthorizeReturnsDeniedIfRoleIsUnknown(): void
    {
        self::assertEquals(
            AuthorizationResult::denied(),
            $this->trainerAuthorization->authorize($this->createStub(AuthenticatedUserInterface::class))
        );
    }


    #[DataProvider('getRole')]
    public function testAuthorizeReturnsGrantedForAllowedRole(Role $role): void
    {
        $authenticatedUserStub = $this->createStub(AuthenticatedUserInterface::class);
        $authenticatedUserStub
            ->method('roleEquals')
            ->willReturnCallback(fn($passedRole): bool => $passedRole === $role);
        $authenticatedUserStub->method('getUserName')->willReturn($this->userName);
        $authenticatedUserStub->method('getRole')->willReturn($role);

        self::assertEquals(
            AuthorizationResult::granted($this->userName, $role),
            $this->trainerAuthorization->authorize($authenticatedUserStub)
        );
    }


    public function testCanCheckActive(): void
    {
        $repoMock = $this->createMock(UserReadingRepository::class);
        $authenticatedUserMock = $this->createMock(AuthenticatedUserInterface::class);

        $authenticatedUserMock->expects($this->once())
            ->method('getUserName')
            ->willReturn($this->userName);

        $repoMock->expects($this->once())
            ->method('checkIfUserExists')
            ->with($this->userName);

        $strategy = new TrainerAuthorizationStrategy($repoMock, $this->url);
        $strategy->checkActive($authenticatedUserMock);
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
        $result = AuthorizationResult::granted($this->userName, Role::MANAGER);

        self::assertEquals(
            LogMessage::fromString('Authorized manager: someUserName'),
            $this->trainerAuthorization->successLogging($result)
        );
    }


    public function testSuccessLoggingThrowsExceptionIfUserNameIsNull(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('UserName is not defined.'));

        $this->trainerAuthorization->successLogging(AuthorizationResult::denied());
    }


    public function testCanGetLogMessageIfUserNameExists(): void
    {
        self::assertEquals(
            LogMessage::fromString('Could not authenticate user for training: ' . $this->userName->asString()),
            $this->trainerAuthorization->infoLogMessageForError($this->userName)
        );
    }


    public function testCanGetLogMessageIfUserNameDoesNotExist(): void
    {
        self::assertEquals(
            LogMessage::fromString('Could not authenticate user for training without user name.'),
            $this->trainerAuthorization->infoLogMessageForError(null)
        );
    }
}
