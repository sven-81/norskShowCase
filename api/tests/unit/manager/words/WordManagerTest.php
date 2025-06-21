<?php

declare(strict_types=1);

namespace norsk\api\manager\words;

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
use norsk\api\tests\provider\WordProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Slim\Routing\Route;
use Slim\Routing\RouteParser;
use Slim\Routing\RoutingResults;

#[CoversClass(WordManager::class)]
class WordManagerTest extends TestCase
{
    private Logger|MockObject $logger;

    private WordReader|MockObject $wordReader;

    private MockObject|ManagerWriter $managerWriter;

    private Url $url;


    protected function setUp(): void
    {
        $this->url = Url::by('http://url');
        $this->logger = $this->createMock(Logger::class);
        $this->wordReader = $this->createMock(WordReader::class);
        $this->managerWriter = $this->createMock(ManagerWriter::class);
    }


    public function testCanGetAllWords(): void
    {
        $_SESSION['user'] = 'someBody';

        $wordManager = new WordManager(
            $this->logger,
            $this->wordReader,
            $this->managerWriter,
            $this->url
        );

        $this->wordReader->expects($this->once())
            ->method('getAllWords')
            ->willReturn($this->getWords());

        $wordsJson = '[' . WordProvider::managedWordArchipelagoAsJsonString() . ']';
        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                LogMessage::fromString(
                    'Generated list of Words: ' . $wordsJson
                    . ' by manager: someBody'
                )
            );

        $json = Json::fromString($wordsJson);
        $expectedWords = VocabularyListResponse::create($this->url, $json);

        $response = $wordManager->getAllWords();
        $this->assertions($expectedWords, $response);
    }


    private function getWords(): Vocabularies
    {
        $word = WordProvider::trainingWordArchipelago();
        $words = Vocabularies::create();
        $words->add($word);

        return $words;
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


    public function testReturnsErrorResponseOnThrownExceptionIfCannotGetAllWords(): void
    {
        $throwable = new RuntimeException('ooops');
        $this->wordReader->expects($this->once())
            ->method('getAllWords')
            ->willThrowException($throwable);

        $this->logger->expects($this->never())
            ->method('info');
        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedWords = ErrorResponse::serverError($this->url, $throwable);

        $_SESSION['user'] = 'someBody';
        $wordManager = new WordManager(
            $this->logger,
            $this->wordReader,
            $this->managerWriter,
            $this->url
        );
        $response = $wordManager->getAllWords();

        $this->assertions($expectedWords, $response);
    }


    public function testCanCreateWord(): void
    {
        $requestMock = $this->createRequest();

        $payload = Payload::of($requestMock);
        $this->wordReader->expects($this->once())
            ->method('ensureWordsAreNotAlreadyPersisted')
            ->with(null, $payload);

        $this->managerWriter->expects($this->once())
            ->method('add')
            ->with($payload);

        $expectedResponse = CreatedResponse::savedVocabulary($this->url);

        $wordManager = new WordManager(
            $this->logger,
            $this->wordReader,
            $this->managerWriter,
            $this->url
        );
        $response = $wordManager->createWord($requestMock);

        $this->assertions($expectedResponse, $response);
    }


    private function createRequest(): MockObject|ServerRequest
    {
        $expectedArray = WordProvider::managedWordArchipelagoAsArray();

        $requestMock = $this->createMock(ServerRequest::class);
        $requestMock->method('getParsedBody')
            ->willReturn($expectedArray);

        return $requestMock;
    }


    public function testReturnsErrorResponseOnThrownExceptionIfCannotCreateWordBecauseItAlreadyExists(): void
    {
        $requestMock = $this->createRequest();

        $throwable = new RuntimeException('ooops', ResponseCode::conflict->value);
        $this->wordReader->expects($this->once())
            ->method('ensureWordsAreNotAlreadyPersisted')
            ->willThrowException($throwable);

        $this->managerWriter->expects($this->never())
            ->method('add');

        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedResponse = ErrorResponse::conflict($this->url, $throwable);

        $wordManager = new WordManager(
            $this->logger,
            $this->wordReader,
            $this->managerWriter,
            $this->url
        );
        $response = $wordManager->createWord($requestMock);

        $this->assertions($expectedResponse, $response);
    }


    public function testReturnsErrorResponseOnThrownExceptionIfCannotCreateWordDueToSomeOtherError(): void
    {
        $requestMock = $this->createRequest();

        $throwable = new RuntimeException('ooops', ResponseCode::badRequest->value);
        $this->wordReader->expects($this->once())
            ->method('ensureWordsAreNotAlreadyPersisted')
            ->willThrowException($throwable);

        $this->managerWriter->expects($this->never())
            ->method('add');

        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedResponse = ErrorResponse::serverError($this->url, $throwable);

        $wordManager = new WordManager(
            $this->logger,
            $this->wordReader,
            $this->managerWriter,
            $this->url
        );
        $response = $wordManager->createWord($requestMock);

        $this->assertions($expectedResponse, $response);
    }


    public function testCanUpdate(): void
    {
        $request = $this->updateRequest();

        $payload = Payload::of($request);
        $id = Id::by(1);
        $this->wordReader->expects($this->once())
            ->method('ensureWordsAreNotAlreadyPersisted')
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

        $wordManager = new WordManager(
            $this->logger,
            $this->wordReader,
            $this->managerWriter,
            $this->url
        );
        $response = $wordManager->update($request);

        $this->assertions($expectedResponse, $response);
    }


    private function updateRequest(): ServerRequest
    {
        $expectedArray = WordProvider::managedWordArchipelagoAsArray();

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


    public function testReturnsErrorResponseIfCannotUpdateWordBecauseItAlreadyExistsForNewVersion(): void
    {
        $request = $this->updateRequest();

        $throwable = new RuntimeException('ooops', ResponseCode::conflict->value);

        $payload = Payload::of($request);
        $id = Id::by(1);
        $this->wordReader->expects($this->once())
            ->method('ensureWordsAreNotAlreadyPersisted')
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

        $wordManager = new WordManager(
            $this->logger,
            $this->wordReader,
            $this->managerWriter,
            $this->url
        );
        $response = $wordManager->update($request);

        $this->assertions($expectedResponse, $response);
    }


    public function testReturnsErrorResponseOnThrownExceptionIfCannotUpdateWordBecauseItIsNotFound(): void
    {
        $request = $this->updateRequest();

        $throwable = new RuntimeException('ooops', ResponseCode::notFound->value);

        $payload = Payload::of($request);
        $id = Id::by(1);
        $this->wordReader->expects($this->once())
            ->method('ensureWordsAreNotAlreadyPersisted')
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

        $wordManager = new WordManager(
            $this->logger,
            $this->wordReader,
            $this->managerWriter,
            $this->url
        );
        $response = $wordManager->update($request);

        $this->assertions($expectedResponse, $response);
    }


    public function testReturnsErrorResponseOnThrownExceptionIfCannotUpdateDueToSomeOtherError(): void
    {
        $request = $this->updateRequest();

        $throwable = new RuntimeException('ooops', ResponseCode::serverError->value);

        $payload = Payload::of($request);
        $id = Id::by(1);
        $this->wordReader->expects($this->once())
            ->method('ensureWordsAreNotAlreadyPersisted')
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

        $wordManager = new WordManager(
            $this->logger,
            $this->wordReader,
            $this->managerWriter,
            $this->url
        );
        $response = $wordManager->update($request);

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

        $json = Json::fromString('{"message":"Removed word with id: 1"}');
        $expectedResponse = SuccessResponse::deletedRecord($this->url, $json);

        $wordManager = new WordManager(
            $this->logger,
            $this->wordReader,
            $this->managerWriter,
            $this->url
        );
        $response = $wordManager->delete($request);

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


    public function testReturnsErrorResponseOnThrownExceptionIfCannotDeleteWordBecauseItIsNotFound(): void
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

        $wordManager = new WordManager(
            $this->logger,
            $this->wordReader,
            $this->managerWriter,
            $this->url
        );
        $response = $wordManager->delete($request);

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

        $wordManager = new WordManager(
            $this->logger,
            $this->wordReader,
            $this->managerWriter,
            $this->url
        );
        $response = $wordManager->delete($request);

        $this->assertions($expectedResponse, $response);
    }


    protected function tearDown(): void
    {
        unset($_SESSION);
    }
}
