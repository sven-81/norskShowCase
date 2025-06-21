<?php

declare(strict_types=1);

namespace norsk\api\user;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use norsk\api\app\logging\Logger;
use norsk\api\app\logging\LogMessage;
use norsk\api\app\request\Parameter;
use norsk\api\app\response\ResponseCode;
use norsk\api\app\response\Url;
use norsk\api\shared\responses\ConflictResponse;
use norsk\api\shared\responses\CreatedResponse;
use norsk\api\shared\responses\ErrorResponse;
use norsk\api\user\exceptions\ParameterMissingException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(Registration::class)]
class RegistrationTest extends TestCase
{
    private Logger|MockObject $loggerMock;

    private UsersWriter|MockObject $writerMock;

    private MockObject|PasswordVector $vectorMock;

    private Url $url;


    protected function setUp(): void
    {
        $this->url = Url::by('http://ulr');
        $this->loggerMock = $this->createMock(Logger::class);
        $this->writerMock = $this->createMock(UsersWriter::class);
        $this->vectorMock = $this->createMock(PasswordVector::class);
    }


    public function testCanRegisterNewUser(): void
    {
        $expectedResponse = CreatedResponse::savedNewUser($this->url);

        $registration = new Registration(
            $this->loggerMock,
            $this->writerMock,
            $this->vectorMock,
            $this->url
        );

        $requestMock = $this->getRequest();

        $this->writerMock->expects($this->once())
            ->method('add');

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
            $this->writerMock,
            $this->vectorMock,
            $this->url
        );

        $requestMock = $this->getRequest(true);

        $this->writerMock->expects($this->never())
            ->method('add');

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
            $this->writerMock,
            $this->vectorMock,
            $this->url
        );

        $requestMock = $this->getRequest();

        $this->writerMock->expects($this->once())
            ->method('add')
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
        $expectedResponse = ConflictResponse::create($this->url);

        $registration = new Registration(
            $this->loggerMock,
            $this->writerMock,
            $this->vectorMock,
            $this->url
        );

        $requestMock = $this->getRequest();

        $this->writerMock->expects($this->once())
            ->method('add')
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
            $this->writerMock,
            $this->vectorMock,
            $this->url
        );

        $requestMock = $this->getRequest();

        $this->writerMock->expects($this->once())
            ->method('add')
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
