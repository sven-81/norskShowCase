<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\web\controller;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use InvalidArgumentException;
use norsk\api\infrastructure\logging\Logger;
use norsk\api\infrastructure\logging\LogMessage;
use norsk\api\shared\application\Json;
use norsk\api\shared\infrastructure\http\request\Payload;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\shared\infrastructure\http\response\responses\ErrorResponse;
use norsk\api\shared\infrastructure\http\response\responses\SuccessResponse;
use norsk\api\shared\infrastructure\http\response\Url;
use norsk\api\user\application\useCases\LoginUser;
use norsk\api\user\application\UserLogin;
use norsk\api\user\domain\model\LoggedInUser;
use norsk\api\user\domain\valueObjects\UserName;
use norsk\api\user\infrastructure\identityAccessManagement\jwt\JsonWebToken;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

#[CoversClass(Login::class)]
class LoginTest extends TestCase
{
    private Logger|MockObject $loggerMock;

    private JsonWebToken $token;

    private Url $url;

    private UserLogin|MockObject $userLoginMock;

    private LoggedInUser|MockObject $loggedInUserMock;

    private string $body;


    protected function setUp(): void
    {
        $this->url = Url::by('http://url');
        $this->loggerMock = $this->createMock(Logger::class);
        $this->userLoginMock = $this->createMock(UserLogin::class);
        $this->loggedInUserMock = $this->createMock(LoggedInUser::class);

        $this->body = '{"username": "someUser","password": "shhhhhhhhhhhhhhhhhhhhhhhhhh"}';
        $this->token = JsonWebToken::fromString('xzJhb.eyJzdWIicCI6MTY1NTIxODAwOX0.ceU31GO4x6QscCNHS4_6GDVq4A');
    }


    public function testCanLogin(): void
    {
        $requestMock = $this->createMock(ServerRequest::class);
        $requestMock->expects($this->exactly(2))
            ->method('getParsedBody')
            ->willReturn(Json::fromString($this->body)->asDecodedJson());

        $matcher = $this->exactly(2);
        $this->loggerMock
            ->expects($matcher)
            ->method('info')
            ->willReturnCallback(
                function (...$args) use ($matcher): void {
                    if ($matcher->numberOfInvocations() === 1) {
                        self::assertEquals([LogMessage::fromString('Getting request for user login')], $args);
                    }
                    if ($matcher->numberOfInvocations() === 2) {
                        self::assertEquals([LogMessage::fromString('User verified successfully: someUser')], $args);
                    }
                }
            );

        $content = Json::fromString(
            '{"login":true,"username":"someUser","firstName":"james","lastName":"last","token":"'
            . $this->token->asString()
            . '"}'
        );

        $command = LoginUser::by(Payload::of($requestMock));

        $this->loggedInUserMock->expects($this->once())
            ->method('getUserName')
            ->willReturn(UserName::by('someUser'));
        $this->loggedInUserMock->expects($this->once())
            ->method('asBodyJson')
            ->willReturn($content);

        $this->userLoginMock->expects($this->once())
            ->method('handle')
            ->with($command)
            ->willReturn($this->loggedInUserMock);

        $login = new Login(
            $this->loggerMock,
            $this->userLoginMock,
            $this->url
        );

        $expected = SuccessResponse::loggedIn($this->url, $content);

        $response = $login->run($requestMock);
        $this->assertion($expected, $response);
    }


    private function assertion(
        Response $expected,
        ResponseInterface $response
    ): void {
        self::assertEquals($expected->getStatusCode(), $response->getStatusCode());
        self::assertEquals($expected->getBody()->getContents(), $response->getBody()->getContents());
    }


    public function testReturnErrorResponseIfCredentialsAreInvalid(): void
    {
        $throwable = new RuntimeException('ooops', ResponseCode::unauthorized->value);

        $requestMock = $this->createMock(ServerRequest::class);
        $requestMock->expects($this->once())
            ->method('getParsedBody')
            ->willReturn(Json::fromString($this->body)->asDecodedJson());

        $this->userLoginMock->expects($this->once())
            ->method('handle')
            ->willThrowException($throwable);

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with(LogMessage::fromString('Getting request for user login'));
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($throwable);

        $this->loggedInUserMock->expects($this->never())
            ->method('getUserName');
        $this->loggedInUserMock->expects($this->never())
            ->method('asBodyJson');

        $login = new Login(
            $this->loggerMock,
            $this->userLoginMock,
            $this->url
        );

        $expected = ErrorResponse::unauthorized($this->url, $throwable);

        $response = $login->run($requestMock);
        $this->assertion($expected, $response);
    }


    public function testReturnErrorResponseIfUserIsNotActive(): void
    {
        $throwable = new RuntimeException('ooops', ResponseCode::forbidden->value);

        $requestMock = $this->createMock(ServerRequest::class);
        $requestMock->expects($this->once())
            ->method('getParsedBody')
            ->willReturn(Json::fromString($this->body)->asDecodedJson());

        $this->userLoginMock->expects($this->once())
            ->method('handle')
            ->willThrowException($throwable);

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with(LogMessage::fromString('Getting request for user login'));
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($throwable);

        $login = new Login(
            $this->loggerMock,
            $this->userLoginMock,
            $this->url
        );

        $expected = ErrorResponse::forbidden($this->url, $throwable);

        $response = $login->run($requestMock);
        $this->assertion($expected, $response);
    }


    public function testReturnErrorResponseIfUserInputIsNotValid(): void
    {
        $throwable = new InvalidArgumentException(
            'The password must be at least 12 characters long.',
            ResponseCode::unprocessable->value
        );

        $body = '{"username": "someUser","password": "shhhh"}';
        $requestMock = $this->createMock(ServerRequest::class);
        $requestMock->expects($this->once())
            ->method('getParsedBody')
            ->willReturn(Json::fromString($body)->asDecodedJson());

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with(LogMessage::fromString('Getting request for user login'));
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($throwable);

        $this->userLoginMock->expects($this->never())
            ->method('handle');

        $login = new Login(
            $this->loggerMock,
            $this->userLoginMock,
            $this->url
        );

        $expected = ErrorResponse::unprocessable($this->url, $throwable);

        $response = $login->run($requestMock);
        $this->assertion($expected, $response);
    }


    public function testReturnErrorResponseIfRequestHasMissingParameters(): void
    {
        $throwable = new RuntimeException('Missing required parameter: username', ResponseCode::badRequest->value);

        $body = '{"password": "myVerySecretlySecret"}';
        $requestMock = $this->createMock(ServerRequest::class);
        $requestMock->expects($this->once())
            ->method('getParsedBody')
            ->willReturn(Json::fromString($body)->asDecodedJson());

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with(LogMessage::fromString('Getting request for user login'));

        $this->loggerMock->expects($this->once())
            ->method('error');

        $this->userLoginMock->expects($this->never())
            ->method('handle');

        $login = new Login(
            $this->loggerMock,
            $this->userLoginMock,
            $this->url
        );

        $expected = ErrorResponse::badRequest($this->url, $throwable);

        $response = $login->run($requestMock);
        $this->assertion($expected, $response);
    }


    public function testReturnErrorResponseOnAnyOtherFailure(): void
    {
        $throwable = new RuntimeException('ooops', ResponseCode::serverError->value);

        $requestMock = $this->createMock(ServerRequest::class);
        $requestMock->expects($this->once())
            ->method('getParsedBody')
            ->willReturn(Json::fromString($this->body)->asDecodedJson());

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with(LogMessage::fromString('Getting request for user login'));
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($throwable);

        $this->userLoginMock->expects($this->once())
            ->method('handle')
            ->willThrowException($throwable);

        $login = new Login(
            $this->loggerMock,
            $this->userLoginMock,
            $this->url
        );

        $expected = ErrorResponse::serverError($this->url, $throwable);

        $response = $login->run($requestMock);
        $this->assertion($expected, $response);
    }
}
