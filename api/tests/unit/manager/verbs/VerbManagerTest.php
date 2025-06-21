<?php

declare(strict_types=1);

namespace norsk\api\manager\verbs;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use norsk\api\app\logging\Logger;
use norsk\api\app\logging\LogMessage;
use norsk\api\app\request\Payload;
use norsk\api\app\response\ResponseCode;
use norsk\api\app\response\Url;
use norsk\api\manager\ManagerWriter;
use norsk\api\manager\responses\VocabularyListResponse;
use norsk\api\shared\Id;
use norsk\api\shared\Json;
use norsk\api\shared\responses\CreatedResponse;
use norsk\api\shared\responses\ErrorResponse;
use norsk\api\shared\responses\NoContentResponse;
use norsk\api\shared\responses\SuccessResponse;
use norsk\api\shared\Vocabularies;
use norsk\api\tests\provider\VerbProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Slim\Routing\Route;
use Slim\Routing\RouteParser;
use Slim\Routing\RoutingResults;

#[CoversClass(VerbManager::class)]
class VerbManagerTest extends TestCase
{
    private Logger|MockObject $logger;

    private VerbReader|MockObject $verbReader;

    private MockObject|ManagerWriter $managerWriter;

    private Url $url;


    protected function setUp(): void
    {
        $this->url = Url::by('http://url');
        $this->logger = $this->createMock(Logger::class);
        $this->verbReader = $this->createMock(VerbReader::class);
        $this->managerWriter = $this->createMock(ManagerWriter::class);
    }


    public function testCanGetAllVerbs(): void
    {
        $_SESSION['user'] = 'someBody';

        $verbManager = new VerbManager(
            $this->logger,
            $this->verbReader,
            $this->managerWriter,
            $this->url
        );

        $this->verbReader->expects($this->once())
            ->method('getAllVerbs')
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

        $response = $verbManager->getAllVerbs();
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
        $this->verbReader->expects($this->once())
            ->method('getAllVerbs')
            ->willThrowException($throwable);

        $this->logger->expects($this->never())
            ->method('info');
        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedVerbs = ErrorResponse::serverError($this->url, $throwable);

        $_SESSION['user'] = 'someBody';
        $verbManager = new VerbManager(
            $this->logger,
            $this->verbReader,
            $this->managerWriter,
            $this->url
        );
        $response = $verbManager->getAllVerbs();

        $this->assertions($expectedVerbs, $response);
    }


    public function testCanCreateVerb(): void
    {
        $requestMock = $this->createRequest();

        $payload = Payload::of($requestMock);
        $this->verbReader->expects($this->once())
            ->method('ensureVerbsAreNotAlreadyPersisted')
            ->with(null, $payload);

        $this->managerWriter->expects($this->once())
            ->method('add')
            ->with($payload);

        $expectedResponse = CreatedResponse::savedVocabulary($this->url);

        $verbManager = new VerbManager(
            $this->logger,
            $this->verbReader,
            $this->managerWriter,
            $this->url
        );
        $response = $verbManager->createVerb($requestMock);

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
        $this->verbReader->expects($this->once())
            ->method('ensureVerbsAreNotAlreadyPersisted')
            ->willThrowException($throwable);

        $this->managerWriter->expects($this->never())
            ->method('add');

        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedResponse = ErrorResponse::conflict($this->url, $throwable);

        $verbManager = new VerbManager(
            $this->logger,
            $this->verbReader,
            $this->managerWriter,
            $this->url
        );
        $response = $verbManager->createVerb($requestMock);

        $this->assertions($expectedResponse, $response);
    }


    public function testReturnsErrorResponseOnThrownExceptionIfCannotCreateVerbDueToSomeOtherError(): void
    {
        $requestMock = $this->createRequest();

        $throwable = new RuntimeException('ooops', ResponseCode::badRequest->value);
        $this->verbReader->expects($this->once())
            ->method('ensureVerbsAreNotAlreadyPersisted')
            ->willThrowException($throwable);

        $this->managerWriter->expects($this->never())
            ->method('add');

        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedResponse = ErrorResponse::serverError($this->url, $throwable);

        $verbManager = new VerbManager(
            $this->logger,
            $this->verbReader,
            $this->managerWriter,
            $this->url
        );
        $response = $verbManager->createVerb($requestMock);

        $this->assertions($expectedResponse, $response);
    }


    public function testCanUpdate(): void
    {
        $request = $this->updateRequest();

        $payload = Payload::of($request);
        $id = Id::by(1);
        $this->verbReader->expects($this->once())
            ->method('ensureVerbsAreNotAlreadyPersisted')
            ->with($id, $payload);

        $this->managerWriter->expects($this->once())
            ->method('update')
            ->with($id, $payload);

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
            $this->verbReader,
            $this->managerWriter,
            $this->url
        );
        $response = $verbManager->update($request);

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
        $parser = $this->createMock(RouteParser::class);
        $results = $this->createMock(RoutingResults::class);
        $route = $this->createMock(Route::class);
        $route->method('getArgument')
            ->willReturn('1');

        $request = $request->withAttribute('__routeParser__', $parser);
        $request = $request->withAttribute('__routingResults__', $results);
        $request = $request->withAttribute('__route__', $route);

        return $request->withParsedBody($expectedArray);
    }


    public function testReturnsErrorResponseIfCannotUpdateVerbBecauseItAlreadyExistsForNewVersion(): void
    {
        $request = $this->updateRequest();

        $throwable = new RuntimeException('ooops', ResponseCode::conflict->value);

        $payload = Payload::of($request);
        $id = Id::by(1);
        $this->verbReader->expects($this->once())
            ->method('ensureVerbsAreNotAlreadyPersisted')
            ->with($id, $payload)
            ->willThrowException($throwable);

        $this->managerWriter->expects($this->never())
            ->method('update');

        $this->logger->expects($this->never())
            ->method('info');
        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedResponse = ErrorResponse::conflict($this->url, $throwable);

        $verbManager = new VerbManager(
            $this->logger,
            $this->verbReader,
            $this->managerWriter,
            $this->url
        );
        $response = $verbManager->update($request);

        $this->assertions($expectedResponse, $response);
    }


    public function testReturnsErrorResponseOnThrownExceptionIfCannotUpdateVerbBecauseItIsNotFound(): void
    {
        $request = $this->updateRequest();

        $throwable = new RuntimeException('ooops', ResponseCode::notFound->value);

        $payload = Payload::of($request);
        $id = Id::by(1);
        $this->verbReader->expects($this->once())
            ->method('ensureVerbsAreNotAlreadyPersisted')
            ->with($id, $payload);

        $this->managerWriter->expects($this->once())
            ->method('update')
            ->willThrowException($throwable);

        $this->logger->expects($this->never())
            ->method('info');
        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedResponse = ErrorResponse::notFound($this->url, $throwable);

        $verbManager = new VerbManager(
            $this->logger,
            $this->verbReader,
            $this->managerWriter,
            $this->url
        );
        $response = $verbManager->update($request);

        $this->assertions($expectedResponse, $response);
    }


    public function testReturnsErrorResponseOnThrownExceptionIfCannotUpdateDueToSomeOtherError(): void
    {
        $request = $this->updateRequest();

        $throwable = new RuntimeException('ooops', ResponseCode::serverError->value);

        $payload = Payload::of($request);
        $id = Id::by(1);
        $this->verbReader->expects($this->once())
            ->method('ensureVerbsAreNotAlreadyPersisted')
            ->with($id, $payload);

        $this->managerWriter->expects($this->once())
            ->method('update')
            ->willThrowException($throwable);

        $this->logger->expects($this->never())
            ->method('info');
        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedResponse = ErrorResponse::serverError($this->url, $throwable);

        $verbManager = new VerbManager(
            $this->logger,
            $this->verbReader,
            $this->managerWriter,
            $this->url
        );
        $response = $verbManager->update($request);

        $this->assertions($expectedResponse, $response);
    }


    public function testCanDelete(): void
    {
        $request = $this->deleteRequest();

        $id = Id::by(1);

        $this->managerWriter->expects($this->once())
            ->method('remove')
            ->with($id);

        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                LogMessage::fromString('Removed Id: 1')
            );

        $json = Json::fromString('{"message":"Removed verb with id: 1"}');
        $expectedResponse = SuccessResponse::deletedRecord($this->url, $json);

        $verbManager = new VerbManager(
            $this->logger,
            $this->verbReader,
            $this->managerWriter,
            $this->url
        );
        $response = $verbManager->delete($request);

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
        $parser = $this->createMock(RouteParser::class);
        $results = $this->createMock(RoutingResults::class);
        $route = $this->createMock(Route::class);
        $route->method('getArgument')
            ->willReturn('1');

        $request = $request->withAttribute('__routeParser__', $parser);
        $request = $request->withAttribute('__routingResults__', $results);

        return $request->withAttribute('__route__', $route);
    }


    public function testReturnsErrorResponseOnThrownExceptionIfCannotDeleteVerbBecauseItIsNotFound(): void
    {
        $request = $this->deleteRequest();

        $id = Id::by(1);

        $throwable = new RuntimeException('ooops', ResponseCode::notFound->value);
        $this->managerWriter->expects($this->once())
            ->method('remove')
            ->with($id)
            ->willThrowException($throwable);

        $this->logger->expects($this->never())
            ->method('info');
        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedResponse = ErrorResponse::notFound($this->url, $throwable);

        $verbManager = new VerbManager(
            $this->logger,
            $this->verbReader,
            $this->managerWriter,
            $this->url
        );
        $response = $verbManager->delete($request);

        $this->assertions($expectedResponse, $response);
    }


    public function testReturnsErrorResponseOnThrownExceptionIfCannotDeleteDueToSomeOtherError(): void
    {
        $request = $this->deleteRequest();

        $id = Id::by(1);

        $throwable = new RuntimeException('ooops', ResponseCode::serverError->value);
        $this->managerWriter->expects($this->once())
            ->method('remove')
            ->with($id)
            ->willThrowException($throwable);

        $this->logger->expects($this->never())
            ->method('info');
        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedResponse = ErrorResponse::serverError($this->url, $throwable);

        $verbManager = new VerbManager(
            $this->logger,
            $this->verbReader,
            $this->managerWriter,
            $this->url
        );
        $response = $verbManager->delete($request);

        $this->assertions($expectedResponse, $response);
    }


    protected function tearDown(): void
    {
        unset($_SESSION);
    }
}
