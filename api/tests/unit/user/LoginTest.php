<?php

declare(strict_types=1);

namespace norsk\api\user;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use InvalidArgumentException;
use norsk\api\app\identityAccessManagement\JsonWebToken;
use norsk\api\app\identityAccessManagement\JwtManagement;
use norsk\api\app\logging\Logger;
use norsk\api\app\logging\LogMessage;
use norsk\api\app\response\ResponseCode;
use norsk\api\app\response\Url;
use norsk\api\shared\Json;
use norsk\api\shared\responses\ErrorResponse;
use norsk\api\shared\responses\SuccessResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

#[CoversClass(Login::class)]
class LoginTest extends TestCase
{
    private Logger|MockObject $loggerMock;

    private UsersReader|MockObject $readerMock;

    private Pepper|MockObject $pepperMock;

    private MockObject|JwtManagement $jwtManagementMock;

    private JsonWebToken $token;

    private Url $url;


    protected function setUp(): void
    {
        $this->url = Url::by('http://url');
        $this->loggerMock = $this->createMock(Logger::class);
        $this->readerMock = $this->createMock(UsersReader::class);
        $this->pepperMock = $this->createMock(Pepper::class);
        $this->jwtManagementMock = $this->createMock(JwtManagement::class);
        $this->token = JsonWebToken::fromString('xzJhb.eyJzdWIicCI6MTY1NTIxODAwOX0.ceU31GO4x6QscCNHS4_6GDVq4A');
    }


    public function testCanLogin(): void
    {
        $body = '{"username": "someUser","password": "shhhhhhhhhhhhhhhhhhhhhhhhhh"}';
        $requestMock = $this->createMock(ServerRequest::class);
        $requestMock->expects($this->once())
            ->method('getParsedBody')
            ->willReturn(Json::fromString($body)->asDecodedJson());

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

        $userName = UserName::by('someUser');
        $validatedUserMock = $this->createMock(ValidatedUser::class);
        $validatedUserMock->expects($this->exactly(2))
            ->method('getUserName')
            ->willReturn($userName);
        $validatedUserMock->expects($this->once())
            ->method('getFirstName')
            ->willReturn(FirstName::by('james'));
        $validatedUserMock->expects($this->once())
            ->method('getLastName')
            ->willReturn(LastName::by('last'));

        $inputPassword = InputPassword::by('shhhhhhhhhhhhhhhhhhhhhhhhhh');

        $this->readerMock->expects($this->once())
            ->method('getDataFor')
            ->with($userName, $inputPassword, $this->pepperMock)
            ->willReturn($validatedUserMock);

        $this->jwtManagementMock->expects($this->once())
            ->method('create')
            ->with($validatedUserMock)
            ->willReturn($this->token);

        $login = new Login(
            $this->loggerMock,
            $this->readerMock,
            $this->jwtManagementMock,
            $this->pepperMock,
            $this->url
        );

        $expected = SuccessResponse::loggedIn(
            $this->url,
            Json::fromString(
                '{"login":true,"username":"someUser","firstName":"james","lastName":"last","token":"'
                . $this->token->asString()
                . '"}'
            )
        );

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

        $body = '{"username": "someUser","password": "shhhhhhhhhhhhhhhhhhhhhhhhhh"}';
        $requestMock = $this->createMock(ServerRequest::class);
        $requestMock->expects($this->once())
            ->method('getParsedBody')
            ->willReturn(Json::fromString($body)->asDecodedJson());

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with(LogMessage::fromString('Getting request for user login'));
        $this->loggerMock->expects($this->exactly(2))
            ->method('error')
            ->with($throwable);

        $userName = UserName::by('someUser');
        $validatedUserMock = $this->createMock(ValidatedUser::class);
        $validatedUserMock->expects($this->never())
            ->method('getUserName');
        $validatedUserMock->expects($this->never())
            ->method('getFirstName');
        $validatedUserMock->expects($this->never())
            ->method('getLastName');

        $inputPassword = InputPassword::by('shhhhhhhhhhhhhhhhhhhhhhhhhh');

        $this->readerMock->expects($this->once())
            ->method('getDataFor')
            ->with($userName, $inputPassword, $this->pepperMock)
            ->willThrowException($throwable);

        $this->jwtManagementMock->expects($this->never())
            ->method('create');

        $login = new Login(
            $this->loggerMock,
            $this->readerMock,
            $this->jwtManagementMock,
            $this->pepperMock,
            $this->url
        );

        $expected = ErrorResponse::unauthorized($this->url, $throwable);

        $response = $login->run($requestMock);
        $this->assertion($expected, $response);
    }


    public function testReturnErrorResponseIfUserIsNotActive(): void
    {
        $throwable = new RuntimeException('ooops', ResponseCode::forbidden->value);

        $body = '{"username": "someUser","password": "shhhhhhhhhhhhhhhhhhhhhhhhhh"}';
        $requestMock = $this->createMock(ServerRequest::class);
        $requestMock->expects($this->once())
            ->method('getParsedBody')
            ->willReturn(Json::fromString($body)->asDecodedJson());

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with(LogMessage::fromString('Getting request for user login'));
        $this->loggerMock->expects($this->exactly(2))
            ->method('error')
            ->with($throwable);

        $userName = UserName::by('someUser');
        $validatedUserMock = $this->createMock(ValidatedUser::class);
        $validatedUserMock->expects($this->never())
            ->method('getUserName');
        $validatedUserMock->expects($this->never())
            ->method('getFirstName');
        $validatedUserMock->expects($this->never())
            ->method('getLastName');

        $inputPassword = InputPassword::by('shhhhhhhhhhhhhhhhhhhhhhhhhh');

        $this->readerMock->expects($this->once())
            ->method('getDataFor')
            ->with($userName, $inputPassword, $this->pepperMock)
            ->willThrowException($throwable);

        $this->jwtManagementMock->expects($this->never())
            ->method('create');

        $login = new Login(
            $this->loggerMock,
            $this->readerMock,
            $this->jwtManagementMock,
            $this->pepperMock,
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
        $this->loggerMock->expects($this->exactly(2))
            ->method('error')
            ->with($throwable);

        $validatedUserMock = $this->createMock(ValidatedUser::class);
        $validatedUserMock->expects($this->never())
            ->method('getUserName');
        $validatedUserMock->expects($this->never())
            ->method('getFirstName');
        $validatedUserMock->expects($this->never())
            ->method('getLastName');

        $this->readerMock->expects($this->never())
            ->method('getDataFor');

        $this->jwtManagementMock->expects($this->never())
            ->method('create');

        $login = new Login(
            $this->loggerMock,
            $this->readerMock,
            $this->jwtManagementMock,
            $this->pepperMock,
            $this->url
        );

        $expected = ErrorResponse::unprocessable($this->url, $throwable);

        $response = $login->run($requestMock);
        $this->assertion($expected, $response);
    }


    public function testReturnErrorResponseIfRequestHasMissingParameters(): void
    {
        $throwable = new RuntimeException('Missing required parameter: userName', ResponseCode::badRequest->value);

        $body = '{"password": "myVerySecretlySecret"}';
        $requestMock = $this->createMock(ServerRequest::class);
        $requestMock->expects($this->once())
            ->method('getParsedBody')
            ->willReturn(Json::fromString($body)->asDecodedJson());

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with(LogMessage::fromString('Getting request for user login'));

        $this->loggerMock->expects($this->exactly(2))
            ->method('error');

        $validatedUserMock = $this->createMock(ValidatedUser::class);
        $validatedUserMock->expects($this->never())
            ->method('getUserName');
        $validatedUserMock->expects($this->never())
            ->method('getFirstName');
        $validatedUserMock->expects($this->never())
            ->method('getLastName');

        $this->readerMock->expects($this->never())
            ->method('getDataFor');

        $this->jwtManagementMock->expects($this->never())
            ->method('create');

        $login = new Login(
            $this->loggerMock,
            $this->readerMock,
            $this->jwtManagementMock,
            $this->pepperMock,
            $this->url
        );

        $expected = ErrorResponse::badRequest($this->url, $throwable);

        $response = $login->run($requestMock);
        $this->assertion($expected, $response);
    }


    public function testReturnErrorResponseOnAnyOtherFailure(): void
    {
        $throwable = new RuntimeException('ooops', ResponseCode::serverError->value);

        $body = '{"username": "someUser","password": "shhhhhhhhhhhhhhhhhhhhhhhhhh"}';
        $requestMock = $this->createMock(ServerRequest::class);
        $requestMock->expects($this->once())
            ->method('getParsedBody')
            ->willReturn(Json::fromString($body)->asDecodedJson());

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with(LogMessage::fromString('Getting request for user login'));
        $this->loggerMock->expects($this->exactly(2))
            ->method('error')
            ->with($throwable);

        $userName = UserName::by('someUser');
        $validatedUserMock = $this->createMock(ValidatedUser::class);
        $validatedUserMock->expects($this->never())
            ->method('getUserName');
        $validatedUserMock->expects($this->never())
            ->method('getFirstName');
        $validatedUserMock->expects($this->never())
            ->method('getLastName');

        $inputPassword = InputPassword::by('shhhhhhhhhhhhhhhhhhhhhhhhhh');

        $this->readerMock->expects($this->once())
            ->method('getDataFor')
            ->with($userName, $inputPassword, $this->pepperMock)
            ->willThrowException($throwable);

        $this->jwtManagementMock->expects($this->never())
            ->method('create');

        $login = new Login(
            $this->loggerMock,
            $this->readerMock,
            $this->jwtManagementMock,
            $this->pepperMock,
            $this->url
        );

        $expected = ErrorResponse::serverError($this->url, $throwable);

        $response = $login->run($requestMock);
        $this->assertion($expected, $response);
    }
}
