<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\web\controller;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use norsk\api\infrastructure\logging\Logger;
use norsk\api\infrastructure\logging\LogMessage;
use norsk\api\shared\infrastructure\http\request\Parameter;
use norsk\api\shared\infrastructure\http\request\Payload;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\shared\infrastructure\http\response\responses\CreatedResponse;
use norsk\api\shared\infrastructure\http\response\responses\ErrorResponse;
use norsk\api\shared\infrastructure\http\response\Url;
use norsk\api\user\application\useCases\RegisterUser;
use norsk\api\user\application\UserRegistration;
use norsk\api\user\domain\exceptions\ParameterMissingException;
use norsk\api\user\domain\model\RegisteredUser;
use norsk\api\user\domain\valueObjects\FirstName;
use norsk\api\user\domain\valueObjects\InputPassword;
use norsk\api\user\domain\valueObjects\LastName;
use norsk\api\user\domain\valueObjects\PasswordVector;
use norsk\api\user\domain\valueObjects\UserName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(Registration::class)]
class RegistrationTest extends TestCase
{
    private Url $url;

    private Logger|MockObject $loggerMock;

    private UserRegistration|MockObject $userRegistration;

    private PasswordVector|MockObject $vectorMock;


    protected function setUp(): void
    {
        $this->url = Url::by('http://ulr');
        $this->loggerMock = $this->createMock(Logger::class);
        $this->userRegistration = $this->createMock(UserRegistration::class);
        $this->vectorMock = $this->createMock(PasswordVector::class);
    }


    public function testCanRegisterNewUser(): void
    {
        $expectedResponse = CreatedResponse::savedNewUser($this->url);

        $registration = new Registration(
            $this->loggerMock,
            $this->userRegistration,
            $this->url
        );

        $requestMock = $this->getRequest();
        $payload = Payload::of($requestMock);
        $command = RegisterUser::by($payload);
        $user = RegisteredUser::create(
            UserName::by('famous12'),
            FirstName::by('James'),
            LastName::by('Last'),
            InputPassword::by('someVeeeeeryExtraLongSecret'),
            $this->vectorMock
        );

        $this->userRegistration->expects($this->once())
            ->method('handle')
            ->with($command)
            ->willReturn($user);

        $matcher = $this->exactly(2);
        $this->loggerMock
            ->expects($matcher)
            ->method('info')
            ->willReturnCallback(
                function (...$args) use ($matcher): void {
                    if ($matcher->numberOfInvocations() === 1) {
                        self::assertEquals([LogMessage::fromString('Getting request for user registration')], $args);
                    }
                    if ($matcher->numberOfInvocations() === 2) {
                        self::assertEquals([LogMessage::fromString('Added new User: famous12')], $args);
                    }
                }
            );

        $this->assertions($registration, $requestMock, $expectedResponse);
    }


    private function getRequest(bool $unset = false): MockObject|ServerRequest
    {
        $expectedArray = [
            'username' => 'famous12',
            'firstName' => 'James',
            'lastName' => 'Last',
            'password' => 'someVeeeeeryExtraLongSecret',
        ];

        if ($unset) {
            unset($expectedArray['firstName']);
        }

        $requestMock = $this->createMock(ServerRequest::class);
        $requestMock->method('getParsedBody')
            ->willReturn($expectedArray);

        return $requestMock;
    }


    private function assertions(
        Registration $registration,
        ServerRequest|MockObject $requestMock,
        Response $expectedResponse
    ): void {
        $response = $registration->registerUser($requestMock);
        self::assertSame(
            $expectedResponse->getStatusCode(),
            $response->getStatusCode()
        );
        self::assertSame(
            $expectedResponse->getBody()->getContents(),
            $response->getBody()->getContents()
        );
    }


    public function testCreatesErrorResponseOnMissingParameter(): void
    {
        $throwable = new ParameterMissingException(Parameter::by('firstName'));
        $expectedResponse = ErrorResponse::badRequest($this->url, $throwable);

        $registration = new Registration(
            $this->loggerMock,
            $this->userRegistration,
            $this->url
        );

        $requestMock = $this->getRequest(true);

        $this->userRegistration->expects($this->never())
            ->method('handle');

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with(LogMessage::fromString('Getting request for user registration'));
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($throwable);

        $this->assertions($registration, $requestMock, $expectedResponse);
    }


    public function testCreatesErrorResponseOnInvalidPassword(): void
    {
        $throwable = new RuntimeException('ooops', ResponseCode::unprocessable->value);
        $expectedResponse = ErrorResponse::unprocessable($this->url, $throwable);

        $registration = new Registration(
            $this->loggerMock,
            $this->userRegistration,
            $this->url
        );

        $requestMock = $this->getRequest();

        $this->userRegistration->expects($this->once())
            ->method('handle')
            ->willThrowException($throwable);

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with(LogMessage::fromString('Getting request for user registration'));
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($throwable);

        $this->assertions($registration, $requestMock, $expectedResponse);
    }


    public function testCreatesErrorResponseIfUserAlreadyExists(): void
    {
        $throwable = new RuntimeException('ooops', 1062);
        $expectedResponse = ErrorResponse::conflict($this->url, $throwable);

        $registration = new Registration(
            $this->loggerMock,
            $this->userRegistration,
            $this->url
        );

        $requestMock = $this->getRequest();

        $this->userRegistration->expects($this->once())
            ->method('handle')
            ->willThrowException($throwable);

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with(LogMessage::fromString('Getting request for user registration'));
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($throwable);

        $this->assertions($registration, $requestMock, $expectedResponse);
    }


    public function testCreatesErrorResponseOnAnyOtherError(): void
    {
        $throwable = new RuntimeException('ooops');
        $expectedResponse = ErrorResponse::serverError($this->url, $throwable);

        $registration = new Registration(
            $this->loggerMock,
            $this->userRegistration,
            $this->url
        );

        $requestMock = $this->getRequest();

        $this->userRegistration->expects($this->once())
            ->method('handle')
            ->willThrowException($throwable);

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with(LogMessage::fromString('Getting request for user registration'));
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($throwable);

        $this->assertions($registration, $requestMock, $expectedResponse);
    }
}
