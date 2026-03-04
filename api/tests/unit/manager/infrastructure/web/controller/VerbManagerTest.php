<?php

declare(strict_types=1);

namespace norsk\api\manager\infrastructure\web\controller;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use norsk\api\infrastructure\logging\Logger;
use norsk\api\infrastructure\logging\LogMessage;
use norsk\api\manager\domain\ManagedVocabularies;
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

    private Id $id;

    private UserName $userName;


    protected function setUp(): void
    {
        $this->url = Url::by('http://url');
        $this->logger = $this->createMock(Logger::class);
        $this->id = Id::by(1);
        $this->userName = UserName::by('someBody');
    }


    private function createVerbManager(
        VerbsProvider $verbsProvider,
        VerbCreator $verbCreator,
        VerbUpdater $verbUpdater,
        VerbRemover $verbRemover,
    ): VerbManager {
        return new VerbManager(
            $this->logger,
            $verbsProvider,
            $verbCreator,
            $verbUpdater,
            $verbRemover,
            $this->url
        );
    }


    public function testCanGetAllVerbs(): void
    {
        $verbsProviderMock = $this->createMock(VerbsProvider::class);
        $authenticatedUserMock = $this->createMock(AuthenticatedUserInterface::class);

        $verbManager = $this->createVerbManager(
            $verbsProviderMock,
            $this->createStub(VerbCreator::class),
            $this->createStub(VerbUpdater::class),
            $this->createStub(VerbRemover::class),
        );

        $authenticatedUserMock->expects($this->once())
            ->method('getUserName')
            ->willReturn($this->userName);

        $command = GetAllVerbs::create();
        $verbsProviderMock->expects($this->once())
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

        $response = $verbManager->getAllVerbs($authenticatedUserMock);
        $this->assertions($expectedVerbs, $response);
    }


    private function getVerbs(): ManagedVocabularies
    {
        $verb = VerbProvider::managedVerbToGo();
        $verbs = ManagedVocabularies::create();
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
        $verbsProviderMock = $this->createMock(VerbsProvider::class);
        $throwable = new RuntimeException('ooops');
        $verbsProviderMock->expects($this->once())
            ->method('handle')
            ->willThrowException($throwable);

        $this->logger->expects($this->never())
            ->method('info');
        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedVerbs = ErrorResponse::serverError($this->url, $throwable);

        $verbManager = $this->createVerbManager(
            $verbsProviderMock,
            $this->createStub(VerbCreator::class),
            $this->createStub(VerbUpdater::class),
            $this->createStub(VerbRemover::class),
        );
        $response = $verbManager->getAllVerbs($this->createStub(AuthenticatedUserInterface::class));

        $this->assertions($expectedVerbs, $response);
    }


    public function testCanCreateVerb(): void
    {
        $verbCreatorMock = $this->createMock(VerbCreator::class);
        $requestStub = $this->createRequestStub();

        $payload = Payload::of($requestStub);
        $command = CreateVerb::createBy($payload);
        $verbCreatorMock->expects($this->once())
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

        $verbManager = $this->createVerbManager(
            $this->createStub(VerbsProvider::class),
            $verbCreatorMock,
            $this->createStub(VerbUpdater::class),
            $this->createStub(VerbRemover::class),
        );
        $response = $verbManager->createVerb($this->createStub(AuthenticatedUserInterface::class), $requestStub);

        $this->assertions($expectedResponse, $response);
    }


    private function createRequestStub(): ServerRequest
    {
        $expectedArray = VerbProvider::managedVerbToGoAsArray();

        $requestStub = $this->createStub(ServerRequest::class);
        $requestStub->method('getParsedBody')
            ->willReturn($expectedArray);

        return $requestStub;
    }


    public function testReturnsErrorResponseOnThrownExceptionIfCannotCreateVerbBecauseItAlreadyExists(): void
    {
        $verbCreatorMock = $this->createMock(VerbCreator::class);
        $requestStub = $this->createRequestStub();

        $throwable = new RuntimeException('ooops', ResponseCode::conflict->value);
        $verbCreatorMock->expects($this->once())
            ->method('handle')
            ->willThrowException($throwable);

        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedResponse = ErrorResponse::conflict($this->url, $throwable);

        $verbManager = $this->createVerbManager(
            $this->createStub(VerbsProvider::class),
            $verbCreatorMock,
            $this->createStub(VerbUpdater::class),
            $this->createStub(VerbRemover::class),
        );
        $response = $verbManager->createVerb($this->createStub(AuthenticatedUserInterface::class), $requestStub);

        $this->assertions($expectedResponse, $response);
    }


    public function testReturnsErrorResponseOnThrownExceptionIfCannotCreateVerbDueToSomeOtherError(): void
    {
        $verbCreatorMock = $this->createMock(VerbCreator::class);
        $requestStub = $this->createRequestStub();

        $throwable = new RuntimeException('ooops', ResponseCode::badRequest->value);
        $verbCreatorMock->expects($this->once())
            ->method('handle')
            ->willThrowException($throwable);

        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);
        $this->logger->expects($this->never())
            ->method('info');

        $expectedResponse = ErrorResponse::serverError($this->url, $throwable);

        $verbManager = $this->createVerbManager(
            $this->createStub(VerbsProvider::class),
            $verbCreatorMock,
            $this->createStub(VerbUpdater::class),
            $this->createStub(VerbRemover::class),
        );
        $response = $verbManager->createVerb($this->createStub(AuthenticatedUserInterface::class), $requestStub);

        $this->assertions($expectedResponse, $response);
    }


    public function testCanUpdate(): void
    {
        $verbUpdaterMock = $this->createMock(VerbUpdater::class);
        $request = $this->updateRequest();
        $payload = Payload::of($request);

        $command = UpdateVerb::createBy($this->id, $payload);
        $verbUpdaterMock->expects($this->once())
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

        $verbManager = $this->createVerbManager(
            $this->createStub(VerbsProvider::class),
            $this->createStub(VerbCreator::class),
            $verbUpdaterMock,
            $this->createStub(VerbRemover::class),
        );
        $response = $verbManager->update($this->createStub(AuthenticatedUserInterface::class), $request);

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
        $verbUpdaterMock = $this->createMock(VerbUpdater::class);
        $request = $this->updateRequest();
        $payload = Payload::of($request);

        $throwable = new RuntimeException('ooops', ResponseCode::conflict->value);

        $command = UpdateVerb::createBy($this->id, $payload);
        $verbUpdaterMock->expects($this->once())
            ->method('handle')
            ->with($command)
            ->willThrowException($throwable);

        $this->logger->expects($this->never())
            ->method('info');
        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedResponse = ErrorResponse::conflict($this->url, $throwable);

        $verbManager = $this->createVerbManager(
            $this->createStub(VerbsProvider::class),
            $this->createStub(VerbCreator::class),
            $verbUpdaterMock,
            $this->createStub(VerbRemover::class),
        );
        $response = $verbManager->update($this->createStub(AuthenticatedUserInterface::class), $request);

        $this->assertions($expectedResponse, $response);
    }


    public function testReturnsErrorResponseOnThrownExceptionIfCannotUpdateVerbBecauseItIsNotFound(): void
    {
        $verbUpdaterMock = $this->createMock(VerbUpdater::class);
        $request = $this->updateRequest();
        $payload = Payload::of($request);

        $throwable = new RuntimeException('ooops', ResponseCode::notFound->value);

        $command = UpdateVerb::createBy($this->id, $payload);
        $verbUpdaterMock->expects($this->once())
            ->method('handle')
            ->with($command)
            ->willThrowException($throwable);

        $this->logger->expects($this->never())
            ->method('info');
        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedResponse = ErrorResponse::notFound($this->url, $throwable);

        $verbManager = $this->createVerbManager(
            $this->createStub(VerbsProvider::class),
            $this->createStub(VerbCreator::class),
            $verbUpdaterMock,
            $this->createStub(VerbRemover::class),
        );
        $response = $verbManager->update($this->createStub(AuthenticatedUserInterface::class), $request);

        $this->assertions($expectedResponse, $response);
    }


    public function testReturnsErrorResponseOnThrownExceptionIfCannotUpdateDueToSomeOtherError(): void
    {
        $verbUpdaterMock = $this->createMock(VerbUpdater::class);
        $request = $this->updateRequest();
        $payload = Payload::of($request);

        $throwable = new RuntimeException('ooops', ResponseCode::serverError->value);

        $command = UpdateVerb::createBy($this->id, $payload);
        $verbUpdaterMock->expects($this->once())
            ->method('handle')
            ->with($command)
            ->willThrowException($throwable);

        $this->logger->expects($this->never())
            ->method('info');
        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedResponse = ErrorResponse::serverError($this->url, $throwable);

        $verbManager = $this->createVerbManager(
            $this->createStub(VerbsProvider::class),
            $this->createStub(VerbCreator::class),
            $verbUpdaterMock,
            $this->createStub(VerbRemover::class),
        );
        $response = $verbManager->update($this->createStub(AuthenticatedUserInterface::class), $request);

        $this->assertions($expectedResponse, $response);
    }


    public function testCanDelete(): void
    {
        $verbRemoverMock = $this->createMock(VerbRemover::class);
        $request = $this->deleteRequest();

        $command = DeleteVerb::createBy($this->id);
        $verbRemoverMock->expects($this->once())
            ->method('handle')
            ->with($command);

        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                LogMessage::fromString('Removed Id: 1')
            );

        $json = Json::fromString('{"message":"Removed verb with id: 1"}');
        $expectedResponse = SuccessResponse::deletedRecord($this->url, $json);

        $verbManager = $this->createVerbManager(
            $this->createStub(VerbsProvider::class),
            $this->createStub(VerbCreator::class),
            $this->createStub(VerbUpdater::class),
            $verbRemoverMock,
        );
        $response = $verbManager->delete($this->createStub(AuthenticatedUserInterface::class), $request);

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
        $verbRemoverMock = $this->createMock(VerbRemover::class);
        $request = $this->deleteRequest();

        $throwable = new RuntimeException('ooops', ResponseCode::notFound->value);

        $command = DeleteVerb::createBy($this->id);
        $verbRemoverMock->expects($this->once())
            ->method('handle')
            ->with($command)
            ->willThrowException($throwable);

        $this->logger->expects($this->never())
            ->method('info');
        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedResponse = ErrorResponse::notFound($this->url, $throwable);

        $verbManager = $this->createVerbManager(
            $this->createStub(VerbsProvider::class),
            $this->createStub(VerbCreator::class),
            $this->createStub(VerbUpdater::class),
            $verbRemoverMock,
        );
        $response = $verbManager->delete($this->createStub(AuthenticatedUserInterface::class), $request);

        $this->assertions($expectedResponse, $response);
    }


    public function testReturnsErrorResponseOnThrownExceptionIfCannotDeleteDueToSomeOtherError(): void
    {
        $verbRemoverMock = $this->createMock(VerbRemover::class);
        $request = $this->deleteRequest();

        $throwable = new RuntimeException('ooops', ResponseCode::serverError->value);

        $command = DeleteVerb::createBy($this->id);
        $verbRemoverMock->expects($this->once())
            ->method('handle')
            ->with($command)
            ->willThrowException($throwable);

        $this->logger->expects($this->never())
            ->method('info');
        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedResponse = ErrorResponse::serverError($this->url, $throwable);

        $verbManager = $this->createVerbManager(
            $this->createStub(VerbsProvider::class),
            $this->createStub(VerbCreator::class),
            $this->createStub(VerbUpdater::class),
            $verbRemoverMock,
        );
        $response = $verbManager->delete($this->createStub(AuthenticatedUserInterface::class), $request);

        $this->assertions($expectedResponse, $response);
    }
}
