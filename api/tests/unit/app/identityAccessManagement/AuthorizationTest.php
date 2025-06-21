<?php

declare(strict_types=1);

namespace norsk\api\app\identityAccessManagement;

use InvalidArgumentException;
use norsk\api\app\logging\Logger;
use norsk\api\app\logging\LogMessage;
use norsk\api\app\response\ResponseCode;
use norsk\api\app\response\UnauthorizedResponse;
use norsk\api\app\response\Url;
use norsk\api\shared\responses\ErrorResponse;
use norsk\api\user\exceptions\NoActiveManagerException;
use norsk\api\user\UserName;
use norsk\api\user\UsersReader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[CoversClass(Authorization::class)]
class AuthorizationTest extends TestCase
{
    private ServerRequestInterface|MockObject $requestMock;

    private Logger|MockObject $loggerMock;

    private UsersReader|MockObject $userReaderMock;

    private MockObject|RequestHandlerInterface $handlerMock;

    private Url $url;


    protected function setUp(): void
    {
        $this->url = Url::by('http://foo');
        $this->loggerMock = $this->createMock(Logger::class);
        $this->userReaderMock = $this->createMock(UsersReader::class);

        $this->requestMock = $this->createMock(ServerRequestInterface::class);
        $this->handlerMock = $this->createMock(RequestHandlerInterface::class);
    }


    public function testCanReturnSuccessfulAuthorization(): void
    {
        $_SESSION['user'] = 'someUsername';
        $_SESSION['scope'] = 'is:manager';
        $userName = $_SESSION['user'];

        $this->handlerMock->expects($this->once())
            ->method('handle')
            ->with($this->requestMock);

        $this->userReaderMock->expects($this->once())
            ->method('isActiveManager')
            ->with(UserName::by($userName));

        $logMessage = LogMessage::fromString('Authenticated manager: ' . $userName);
        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with($logMessage);

        $authorization = new Authorization($this->loggerMock, $this->userReaderMock, $this->url);
        $authorization->process($this->requestMock, $this->handlerMock);
    }


    public function testCanReturnUnauthorizedResponseIfScopeIsNotManager(): void
    {
        $_SESSION['user'] = 'someUsername';
        $_SESSION['scope'] = 'is:noManager';

        $this->handlerMock->expects($this->never())
            ->method('handle');

        $this->userReaderMock->expects($this->never())
            ->method('isActiveManager');

        $this->loggerMock->expects($this->never())
            ->method('info');

        $authorization = new Authorization($this->loggerMock, $this->userReaderMock, $this->url);
        $response = $authorization->process($this->requestMock, $this->handlerMock);

        self::assertEquals(
            UnauthorizedResponse::noRights($this->url)->getStatusCode(),
            $response->getStatusCode()
        );
        self::assertEquals(
            UnauthorizedResponse::noRights($this->url)->getBody()->getContents(),
            $response->getBody()->getContents()
        );
    }


    public function testCanReturnUnauthorizedResponseIfUserIsNotInSession(): void
    {
        $_SESSION['scope'] = 'is:noManager';

        $this->handlerMock->expects($this->never())
            ->method('handle');

        $this->userReaderMock->expects($this->never())
            ->method('isActiveManager');

        $this->loggerMock->expects($this->never())
            ->method('info');

        $authorization = new Authorization($this->loggerMock, $this->userReaderMock, $this->url);
        $response = $authorization->process($this->requestMock, $this->handlerMock);

        self::assertEquals(
            UnauthorizedResponse::noRights($this->url)->getStatusCode(),
            $response->getStatusCode()
        );
        self::assertEquals(
            UnauthorizedResponse::noRights($this->url)->getBody()->getContents(),
            $response->getBody()->getContents()
        );
    }


    public function testCanHandleErrorIfUserNameExists(): void
    {
        $_SESSION['user'] = 'someUsername';
        $_SESSION['scope'] = 'is:manager';
        $userName = $_SESSION['user'];

        $this->handlerMock->expects($this->never())
            ->method('handle');

        $exception = new NoActiveManagerException('not active');
        $this->userReaderMock->expects($this->once())
            ->method('isActiveManager')
            ->with(UserName::by($userName))
            ->willThrowException($exception);

        $logMessage = LogMessage::fromString('Could not authenticate manager: someUsername');
        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with($logMessage);
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($exception);

        $authorization = new Authorization($this->loggerMock, $this->userReaderMock, $this->url);
        $response = $authorization->process($this->requestMock, $this->handlerMock);

        self::assertEquals(
            ErrorResponse::unauthorized($this->url, $exception)->getStatusCode(),
            $response->getStatusCode()
        );
        self::assertEquals(
            ErrorResponse::unauthorized($this->url, $exception)->getBody()->getContents(),
            $response->getBody()->getContents()
        );
    }


    public function testCanHandleErrorIfUserNameIsEmpty(): void
    {
        $_SESSION['user'] = ' ';
        $_SESSION['scope'] = 'is:manager';

        $this->handlerMock->expects($this->never())
            ->method('handle');

        $exception = new InvalidArgumentException(
            'The username must be between 4 and 30 characters long.',
            ResponseCode::unprocessable->value
        );
        $this->userReaderMock->expects($this->never())
            ->method('isActiveManager');

        $logMessage = LogMessage::fromString('Could not authenticate manager without user name.');
        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with($logMessage);
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($exception);

        $authorization = new Authorization($this->loggerMock, $this->userReaderMock, $this->url);
        $response = $authorization->process($this->requestMock, $this->handlerMock);

        self::assertEquals(
            ErrorResponse::unauthorized($this->url, $exception)->getStatusCode(),
            $response->getStatusCode()
        );
        self::assertEquals(
            ErrorResponse::unauthorized($this->url, $exception)->getBody()->getContents(),
            $response->getBody()->getContents()
        );
    }


    protected function tearDown(): void
    {
        unset($_SESSION);
    }
}
