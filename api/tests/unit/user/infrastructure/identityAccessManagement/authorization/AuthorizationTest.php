<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\identityAccessManagement\authorization;

use InvalidArgumentException;
use norsk\api\infrastructure\logging\Logger;
use norsk\api\infrastructure\logging\LogMessage;
use norsk\api\shared\infrastructure\http\request\Payload;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\shared\infrastructure\http\response\responses\ErrorResponse;
use norsk\api\shared\infrastructure\http\response\UnauthorizedResponse;
use norsk\api\shared\infrastructure\http\response\Url;
use norsk\api\user\domain\model\JwtAuthenticatedUser;
use norsk\api\user\domain\service\AuthorizationStrategy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use stdClass;

#[CoversClass(Authorization::class)]
class AuthorizationTest extends TestCase
{
    private ServerRequestInterface|MockObject $requestMock;

    private Logger|MockObject $loggerMock;

    private AuthorizationStrategy|MockObject $strategyMock;

    private MockObject|RequestHandlerInterface $handlerMock;

    private Url $url;

    private AuthorizationDecision|MockObject $authorizedDecisionMock;

    private string $userName;

    private JwtAuthenticatedUser $authenticatedUser;


    protected function setUp(): void
    {
        $this->url = Url::by('http://foo');
        $this->loggerMock = $this->createMock(Logger::class);
        $this->strategyMock = $this->createMock(AuthorizationStrategy::class);

        $this->requestMock = $this->createMock(ServerRequestInterface::class);
        $this->handlerMock = $this->createMock(RequestHandlerInterface::class);

        $this->authorizedDecisionMock = $this->createMock(AuthorizationDecision::class);

        $this->userName = 'someUsername';
        $class = new stdClass();
        $class->nickname = $this->userName;
        $class->scope = 'is:user';
        $payload = Payload::by($class);

        $this->authenticatedUser = JwtAuthenticatedUser::byPayload($payload);
    }


    public function testCanReturnSuccessfulAuthorization(): void
    {
        $this->requestMock->expects($this->once())
            ->method('getAttribute')
            ->willReturn($this->authenticatedUser);

        $this->handlerMock->expects($this->once())
            ->method('handle')
            ->with($this->requestMock);

        $this->authorizedDecisionMock->expects($this->once())
            ->method('failed')
            ->willReturn(false);

        $logMessage = LogMessage::fromString('Authenticated user: ' . $this->userName);

        $this->strategyMock->expects($this->once())
            ->method('authorize')
            ->with($this->authenticatedUser)
            ->willReturn($this->authorizedDecisionMock);
        $this->strategyMock->expects($this->once())
            ->method('checkActive')
            ->with($this->authenticatedUser);
        $this->strategyMock->expects($this->once())
            ->method('successLogging')
            ->with($this->authorizedDecisionMock)
            ->willReturn($logMessage);

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with($logMessage);

        $authorization = new Authorization($this->loggerMock, $this->strategyMock, $this->url);
        $authorization->process($this->requestMock, $this->handlerMock);
    }


    public function testCanReturnUnauthorizedResponseIfAuthorizationFails(): void
    {
        $this->handlerMock->expects($this->never())
            ->method('handle');

        $this->requestMock->expects($this->once())
            ->method('getAttribute')
            ->willReturn($this->authenticatedUser);

        $this->authorizedDecisionMock->expects($this->once())
            ->method('failed')
            ->willReturn(true);

        $this->strategyMock->expects($this->once())
            ->method('authorize')
            ->with($this->authenticatedUser)
            ->willReturn($this->authorizedDecisionMock);

        $this->strategyMock->expects($this->never())
            ->method('checkActive');
        $this->strategyMock->expects($this->never())
            ->method('successLogging');

        $this->strategyMock->expects($this->once())
            ->method('unauthorizedResponse')
            ->willReturn(UnauthorizedResponse::noManagingRights($this->url));

        $this->loggerMock->expects($this->never())
            ->method('info');

        $authorization = new Authorization($this->loggerMock, $this->strategyMock, $this->url);
        $response = $authorization->process($this->requestMock, $this->handlerMock);

        self::assertEquals(
            UnauthorizedResponse::noManagingRights($this->url)->getStatusCode(),
            $response->getStatusCode()
        );
        self::assertEquals(
            UnauthorizedResponse::noManagingRights($this->url)->getBody()->getContents(),
            $response->getBody()->getContents()
        );
    }


    public function testCanHandleErrorWithEmptyUserName(): void
    {
        $exception = new InvalidArgumentException(
            'The username must be between 4 and 30 characters long.',
            ResponseCode::unprocessable->value
        );
        $logMessage = LogMessage::fromString('Could not authenticate manager without user name.');

        $this->requestMock->expects($this->once())
            ->method('getAttribute')
            ->willReturn($this->authenticatedUser);

        $this->strategyMock->expects($this->once())
            ->method('authorize')
            ->willThrowException($exception);

        $this->authorizedDecisionMock->expects($this->never())
            ->method('failed');

        $this->handlerMock->expects($this->never())
            ->method('handle');

        $this->strategyMock->expects($this->once())
            ->method('infoLogMessageForError')
            ->with(null)
            ->willReturn($logMessage);

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with($logMessage);
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($exception);

        $authorization = new Authorization($this->loggerMock, $this->strategyMock, $this->url);
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
}
