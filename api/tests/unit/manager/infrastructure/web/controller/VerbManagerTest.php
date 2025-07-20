<?php

declare(strict_types=1);

namespace norsk\api\manager\infrastructure\web\controller;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use norsk\api\infrastructure\logging\Logger;
use norsk\api\infrastructure\logging\LogMessage;
use norsk\api\manager\application\verbManaging\useCases\CreateVerb;
use norsk\api\manager\application\verbManaging\useCases\DeleteVerb;
use norsk\api\manager\application\verbManaging\useCases\GetAllVerbs;
use norsk\api\manager\application\verbManaging\useCases\UpdateVerb;
use norsk\api\manager\application\verbManaging\VerbCreator;
use norsk\api\manager\application\verbManaging\VerbRemover;
use norsk\api\manager\application\verbManaging\VerbsProvider;
use norsk\api\manager\application\verbManaging\VerbUpdater;
use norsk\api\manager\infrastructure\web\responses\VocabularyListResponse;
use norsk\api\shared\application\Json;
use norsk\api\shared\domain\Id;
use norsk\api\shared\domain\Vocabularies;
use norsk\api\shared\infrastructure\http\request\Payload;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\shared\infrastructure\http\response\responses\CreatedResponse;
use norsk\api\shared\infrastructure\http\response\responses\ErrorResponse;
use norsk\api\shared\infrastructure\http\response\responses\NoContentResponse;
use norsk\api\shared\infrastructure\http\response\responses\SuccessResponse;
use norsk\api\shared\infrastructure\http\response\Url;
use norsk\api\tests\provider\VerbProvider;
use norsk\api\user\application\AuthenticatedUserInterface;
use norsk\api\user\domain\valueObjects\UserName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;


#[CoversClass(VerbManager::class)]
class VerbManagerTest extends TestCase
{
    private Logger|MockObject $logger;

    private Url $url;

    private VerbsProvider|MockObject $verbsProviderMock;

    private VerbCreator|MockObject $verbCreatorMock;

    private VerbUpdater|MockObject $verbUpdaterMock;

    private VerbRemover|MockObject $verbRemoverMock;

    private Id $id;

    private AuthenticatedUserInterface|MockObject $authenticatedUserMock;

    private UserName $userName;


    protected function setUp(): void
    {
        $this->url = Url::by('http://url');
        $this->logger = $this->createMock(Logger::class);
        $this->id = Id::by(1);
        $this->userName = UserName::by('someBody');

        $this->authenticatedUserMock = $this->createMock(AuthenticatedUserInterface::class);

        $this->verbsProviderMock = $this->createMock(VerbsProvider::class);
        $this->verbCreatorMock = $this->createMock(VerbCreator::class);
        $this->verbUpdaterMock = $this->createMock(VerbUpdater::class);
        $this->verbRemoverMock = $this->createMock(VerbRemover::class);
    }


    public function testCanGetAllVerbs(): void
    {
        $verbManager = new VerbManager(
            $this->logger,
            $this->verbsProviderMock,
            $this->verbCreatorMock,
            $this->verbUpdaterMock,
            $this->verbRemoverMock,
            $this->url
        );

        $this->authenticatedUserMock->expects($this->once())
            ->method('getUserName')
            ->willReturn($this->userName);

        $command = GetAllVerbs::create();
        $this->verbsProviderMock->expects($this->once())
            ->method('handle')
            ->with($command)
            ->willReturn($this->getVerbs());

        $verbsJson = '[{"id":1,"german":"gehen","norsk":"g\u00e5","norskPresent":"g\u00e5r",'
                     . '"norskPast":"gikk","norskPastPerfect":"har g\u00e5tt"}]';
        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                LogMessage::fromString(
                    'Generated list of Verbs: ' . $verbsJson
                    . ' by manager: someBody'
                )
            );

        $json = Json::fromString($verbsJson);
        $expectedVerbs = VocabularyListResponse::create($this->url, $json);

        $response = $verbManager->getAllVerbs($this->authenticatedUserMock);
        $this->assertions($expectedVerbs, $response);
    }


    private function getVerbs(): Vocabularies
    {
        $verb = VerbProvider::managedVerbToGo();
        $verbs = Vocabularies::create();
        $verbs->add($verb);

        return $verbs;
    }


    private function assertions(
        Response $expectedResponse,
        ResponseInterface $response
    ): void {
        self::assertEquals(
            $expectedResponse->getStatusCode(),
            $response->getStatusCode()
        );
        self::assertEquals(
            $expectedResponse->getBody()->getContents(),
            $response->getBody()->getContents()
        );
    }


    public function testReturnsErrorResponseOnThrownExceptionIfCannotGetAllVerbs(): void
    {
        $throwable = new RuntimeException('ooops');
        $this->verbsProviderMock->expects($this->once())
            ->method('handle')
            ->willThrowException($throwable);

        $this->logger->expects($this->never())
            ->method('info');
        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedVerbs = ErrorResponse::serverError($this->url, $throwable);

        $verbManager = new VerbManager(
            $this->logger,
            $this->verbsProviderMock,
            $this->verbCreatorMock,
            $this->verbUpdaterMock,
            $this->verbRemoverMock,
            $this->url
        );
        $response = $verbManager->getAllVerbs($this->authenticatedUserMock);

        $this->assertions($expectedVerbs, $response);
    }


    public function testCanCreateVerb(): void
    {
        $requestMock = $this->createRequest();

        $payload = Payload::of($requestMock);
        $command = CreateVerb::createBy($payload);
        $this->verbCreatorMock->expects($this->once())
            ->method('handle')
            ->with($command);

        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                LogMessage::fromString(
                    'Created Verb: ' . $payload->asJson()->asString()
                )
            );

        $expectedResponse = CreatedResponse::savedVocabulary($this->url);

        $verbManager = new VerbManager(
            $this->logger,
            $this->verbsProviderMock,
            $this->verbCreatorMock,
            $this->verbUpdaterMock,
            $this->verbRemoverMock,
            $this->url
        );
        $response = $verbManager->createVerb($this->authenticatedUserMock, $requestMock);

        $this->assertions($expectedResponse, $response);
    }


    private function createRequest(): MockObject|ServerRequest
    {
        $expectedArray = VerbProvider::managedVerbToGoAsArray();

        $requestMock = $this->createMock(ServerRequest::class);
        $requestMock->method('getParsedBody')
            ->willReturn($expectedArray);

        return $requestMock;
    }


    public function testReturnsErrorResponseOnThrownExceptionIfCannotCreateVerbBecauseItAlreadyExists(): void
    {
        $requestMock = $this->createRequest();

        $throwable = new RuntimeException('ooops', ResponseCode::conflict->value);
        $this->verbCreatorMock->expects($this->once())
            ->method('handle')
            ->willThrowException($throwable);

        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedResponse = ErrorResponse::conflict($this->url, $throwable);

        $verbManager = new VerbManager(
            $this->logger,
            $this->verbsProviderMock,
            $this->verbCreatorMock,
            $this->verbUpdaterMock,
            $this->verbRemoverMock,
            $this->url
        );
        $response = $verbManager->createVerb($this->authenticatedUserMock, $requestMock);

        $this->assertions($expectedResponse, $response);
    }


    public function testReturnsErrorResponseOnThrownExceptionIfCannotCreateVerbDueToSomeOtherError(): void
    {
        $requestMock = $this->createRequest();

        $throwable = new RuntimeException('ooops', ResponseCode::badRequest->value);
        $this->verbCreatorMock->expects($this->once())
            ->method('handle')
            ->willThrowException($throwable);

        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);
        $this->logger->expects($this->never())
            ->method('info');

        $expectedResponse = ErrorResponse::serverError($this->url, $throwable);

        $verbManager = new VerbManager(
            $this->logger,
            $this->verbsProviderMock,
            $this->verbCreatorMock,
            $this->verbUpdaterMock,
            $this->verbRemoverMock,
            $this->url
        );
        $response = $verbManager->createVerb($this->authenticatedUserMock, $requestMock);

        $this->assertions($expectedResponse, $response);
    }


    public function testCanUpdate(): void
    {
        $request = $this->updateRequest();
        $payload = Payload::of($request);

        $command = UpdateVerb::createBy($this->id, $payload);
        $this->verbUpdaterMock->expects($this->once())
            ->method('handle')
            ->with($command);

        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                LogMessage::fromString(
                    'Updated Id: 1'
                    . ' to: ' . $payload->asJson()->asString()
                )
            );

        $expectedResponse = NoContentResponse::updatedVocabularySuccessfully($this->url);

        $verbManager = new VerbManager(
            $this->logger,
            $this->verbsProviderMock,
            $this->verbCreatorMock,
            $this->verbUpdaterMock,
            $this->verbRemoverMock,
            $this->url
        );
        $response = $verbManager->update($this->authenticatedUserMock, $request);

        $this->assertions($expectedResponse, $response);
    }


    private function updateRequest(): ServerRequest
    {
        $expectedArray = VerbProvider::managedVerbToGoAsArray();

        $request = new ServerRequest(
            method: 'put',
            uri: 'foo',
            headers: [],
            body: Json::encodeFromArray($expectedArray)->asString()
        );

        return $request
            ->withAttribute(attribute: 'id', value: '1')
            ->withParsedBody($expectedArray);
    }


    public function testReturnsErrorResponseIfCannotUpdateVerbBecauseItAlreadyExistsForNewVersion(): void
    {
        $request = $this->updateRequest();
        $payload = Payload::of($request);

        $throwable = new RuntimeException('ooops', ResponseCode::conflict->value);

        $command = UpdateVerb::createBy($this->id, $payload);
        $this->verbUpdaterMock->expects($this->once())
            ->method('handle')
            ->with($command)
            ->willThrowException($throwable);

        $this->logger->expects($this->never())
            ->method('info');
        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedResponse = ErrorResponse::conflict($this->url, $throwable);

        $verbManager = new VerbManager(
            $this->logger,
            $this->verbsProviderMock,
            $this->verbCreatorMock,
            $this->verbUpdaterMock,
            $this->verbRemoverMock,
            $this->url
        );
        $response = $verbManager->update($this->authenticatedUserMock, $request);

        $this->assertions($expectedResponse, $response);
    }


    public function testReturnsErrorResponseOnThrownExceptionIfCannotUpdateVerbBecauseItIsNotFound(): void
    {
        $request = $this->updateRequest();
        $payload = Payload::of($request);

        $throwable = new RuntimeException('ooops', ResponseCode::notFound->value);

        $command = UpdateVerb::createBy($this->id, $payload);
        $this->verbUpdaterMock->expects($this->once())
            ->method('handle')
            ->with($command)
            ->willThrowException($throwable);

        $this->logger->expects($this->never())
            ->method('info');
        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedResponse = ErrorResponse::notFound($this->url, $throwable);

        $verbManager = new VerbManager(
            $this->logger,
            $this->verbsProviderMock,
            $this->verbCreatorMock,
            $this->verbUpdaterMock,
            $this->verbRemoverMock,
            $this->url
        );
        $response = $verbManager->update($this->authenticatedUserMock, $request);

        $this->assertions($expectedResponse, $response);
    }


    public function testReturnsErrorResponseOnThrownExceptionIfCannotUpdateDueToSomeOtherError(): void
    {
        $request = $this->updateRequest();
        $payload = Payload::of($request);

        $throwable = new RuntimeException('ooops', ResponseCode::serverError->value);

        $command = UpdateVerb::createBy($this->id, $payload);
        $this->verbUpdaterMock->expects($this->once())
            ->method('handle')
            ->with($command)
            ->willThrowException($throwable);

        $this->logger->expects($this->never())
            ->method('info');
        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedResponse = ErrorResponse::serverError($this->url, $throwable);

        $verbManager = new VerbManager(
            $this->logger,
            $this->verbsProviderMock,
            $this->verbCreatorMock,
            $this->verbUpdaterMock,
            $this->verbRemoverMock,
            $this->url
        );
        $response = $verbManager->update($this->authenticatedUserMock, $request);

        $this->assertions($expectedResponse, $response);
    }


    public function testCanDelete(): void
    {
        $request = $this->deleteRequest();

        $command = DeleteVerb::createBy($this->id);
        $this->verbRemoverMock->expects($this->once())
            ->method('handle')
            ->with($command);

        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                LogMessage::fromString('Removed Id: 1')
            );

        $json = Json::fromString('{"message":"Removed verb with id: 1"}');
        $expectedResponse = SuccessResponse::deletedRecord($this->url, $json);

        $verbManager = new VerbManager(
            $this->logger,
            $this->verbsProviderMock,
            $this->verbCreatorMock,
            $this->verbUpdaterMock,
            $this->verbRemoverMock,
            $this->url
        );
        $response = $verbManager->delete($this->authenticatedUserMock, $request);

        $this->assertions($expectedResponse, $response);
    }


    private function deleteRequest(): ServerRequest
    {
        $request = new ServerRequest(
            method: 'delete',
            uri: 'foo',
            headers: [],
            body: '{}'
        );

        return $request->withAttribute('id', '1');
    }


    public function testReturnsErrorResponseOnThrownExceptionIfCannotDeleteVerbBecauseItIsNotFound(): void
    {
        $request = $this->deleteRequest();

        $throwable = new RuntimeException('ooops', ResponseCode::notFound->value);

        $command = DeleteVerb::createBy($this->id);
        $this->verbRemoverMock->expects($this->once())
            ->method('handle')
            ->with($command)
            ->willThrowException($throwable);

        $this->logger->expects($this->never())
            ->method('info');
        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedResponse = ErrorResponse::notFound($this->url, $throwable);

        $verbManager = new VerbManager(
            $this->logger,
            $this->verbsProviderMock,
            $this->verbCreatorMock,
            $this->verbUpdaterMock,
            $this->verbRemoverMock,
            $this->url
        );
        $response = $verbManager->delete($this->authenticatedUserMock, $request);

        $this->assertions($expectedResponse, $response);
    }


    public function testReturnsErrorResponseOnThrownExceptionIfCannotDeleteDueToSomeOtherError(): void
    {
        $request = $this->deleteRequest();

        $throwable = new RuntimeException('ooops', ResponseCode::serverError->value);

        $command = DeleteVerb::createBy($this->id);
        $this->verbRemoverMock->expects($this->once())
            ->method('handle')
            ->with($command)
            ->willThrowException($throwable);

        $this->logger->expects($this->never())
            ->method('info');
        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedResponse = ErrorResponse::serverError($this->url, $throwable);

        $verbManager = new VerbManager(
            $this->logger,
            $this->verbsProviderMock,
            $this->verbCreatorMock,
            $this->verbUpdaterMock,
            $this->verbRemoverMock,
            $this->url
        );
        $response = $verbManager->delete($this->authenticatedUserMock, $request);

        $this->assertions($expectedResponse, $response);
    }
}
