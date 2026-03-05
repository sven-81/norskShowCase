<?php

declare(strict_types=1);

namespace norsk\api\manager\infrastructure\web\controller;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use norsk\api\infrastructure\logging\Logger;
use norsk\api\infrastructure\logging\LogMessage;
use norsk\api\manager\application\wordManaging\useCases\CreateWord;
use norsk\api\manager\application\wordManaging\useCases\DeleteWord;
use norsk\api\manager\application\wordManaging\useCases\GetAllWords;
use norsk\api\manager\application\wordManaging\useCases\UpdateWord;
use norsk\api\manager\application\wordManaging\WordCreator;
use norsk\api\manager\application\wordManaging\WordRemover;
use norsk\api\manager\application\wordManaging\WordsProvider;
use norsk\api\manager\application\wordManaging\WordUpdater;
use norsk\api\manager\domain\ManagedVocabularies;
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
use norsk\api\tests\provider\WordProvider;
use norsk\api\user\application\AuthenticatedUserInterface;
use norsk\api\user\domain\valueObjects\UserName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

#[CoversClass(WordManager::class)]
class WordManagerTest extends TestCase
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


    private function createWordManager(
        WordsProvider $wordsProvider,
        WordCreator $wordCreator,
        WordUpdater $wordUpdater,
        WordRemover $wordRemover,
    ): WordManager {
        return new WordManager(
            $this->logger,
            $wordsProvider,
            $wordCreator,
            $wordUpdater,
            $wordRemover,
            $this->url
        );
    }


    public function testCanGetAllWords(): void
    {
        $wordsProviderMock = $this->createMock(WordsProvider::class);
        $authenticatedUserMock = $this->createMock(AuthenticatedUserInterface::class);

        $wordManager = $this->createWordManager(
            $wordsProviderMock,
            $this->createStub(WordCreator::class),
            $this->createStub(WordUpdater::class),
            $this->createStub(WordRemover::class),
        );

        $authenticatedUserMock->expects($this->once())
            ->method('getUserName')
            ->willReturn($this->userName);

        $command = GetAllWords::create();
        $wordsProviderMock->expects($this->once())
            ->method('handle')
            ->with($command)
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

        $response = $wordManager->getAllWords($authenticatedUserMock);
        $this->assertions($expectedWords, $response);
    }


    private function getWords(): ManagedVocabularies
    {
        $word = WordProvider::managedWordArchipelago();
        $words = ManagedVocabularies::create();
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
        $wordsProviderMock = $this->createMock(WordsProvider::class);
        $throwable = new RuntimeException('ooops');
        $wordsProviderMock->expects($this->once())
            ->method('handle')
            ->willThrowException($throwable);

        $this->logger->expects($this->never())
            ->method('info');
        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedWords = ErrorResponse::serverError($this->url, $throwable);

        $wordManager = $this->createWordManager(
            $wordsProviderMock,
            $this->createStub(WordCreator::class),
            $this->createStub(WordUpdater::class),
            $this->createStub(WordRemover::class),
        );
        $response = $wordManager->getAllWords($this->createStub(AuthenticatedUserInterface::class));

        $this->assertions($expectedWords, $response);
    }


    public function testCanCreateWord(): void
    {
        $wordCreatorMock = $this->createMock(WordCreator::class);
        $requestStub = $this->createRequestStub();

        $payload = Payload::of($requestStub);
        $command = CreateWord::createBy($payload);
        $wordCreatorMock->expects($this->once())
            ->method('handle')
            ->with($command);

        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                LogMessage::fromString(
                    'Created Word: ' . $payload->asJson()->asString()
                )
            );

        $expectedResponse = CreatedResponse::savedVocabulary($this->url);

        $wordManager = $this->createWordManager(
            $this->createStub(WordsProvider::class),
            $wordCreatorMock,
            $this->createStub(WordUpdater::class),
            $this->createStub(WordRemover::class),
        );
        $response = $wordManager->createWord($this->createStub(AuthenticatedUserInterface::class), $requestStub);

        $this->assertions($expectedResponse, $response);
    }


    private function createRequestStub(): ServerRequest
    {
        $expectedArray = WordProvider::managedWordArchipelagoAsArray();

        $requestStub = $this->createStub(ServerRequest::class);
        $requestStub->method('getParsedBody')
            ->willReturn($expectedArray);

        return $requestStub;
    }


    public function testReturnsErrorResponseOnThrownExceptionIfCannotCreateWordBecauseItAlreadyExists(): void
    {
        $wordCreatorMock = $this->createMock(WordCreator::class);
        $requestStub = $this->createRequestStub();

        $throwable = new RuntimeException('ooops', ResponseCode::conflict->value);
        $wordCreatorMock->expects($this->once())
            ->method('handle')
            ->willThrowException($throwable);

        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);
        $this->logger->expects($this->never())
            ->method('info');

        $expectedResponse = ErrorResponse::conflict($this->url, $throwable);

        $wordManager = $this->createWordManager(
            $this->createStub(WordsProvider::class),
            $wordCreatorMock,
            $this->createStub(WordUpdater::class),
            $this->createStub(WordRemover::class),
        );
        $response = $wordManager->createWord($this->createStub(AuthenticatedUserInterface::class), $requestStub);

        $this->assertions($expectedResponse, $response);
    }


    public function testReturnsErrorResponseOnThrownExceptionIfCannotCreateWordDueToSomeOtherError(): void
    {
        $wordCreatorMock = $this->createMock(WordCreator::class);
        $requestStub = $this->createRequestStub();

        $throwable = new RuntimeException('ooops', ResponseCode::badRequest->value);
        $wordCreatorMock->expects($this->once())
            ->method('handle')
            ->willThrowException($throwable);

        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);
        $this->logger->expects($this->never())
            ->method('info');

        $expectedResponse = ErrorResponse::serverError($this->url, $throwable);

        $wordManager = $this->createWordManager(
            $this->createStub(WordsProvider::class),
            $wordCreatorMock,
            $this->createStub(WordUpdater::class),
            $this->createStub(WordRemover::class),
        );
        $response = $wordManager->createWord($this->createStub(AuthenticatedUserInterface::class), $requestStub);

        $this->assertions($expectedResponse, $response);
    }


    public function testCanUpdate(): void
    {
        $wordUpdaterMock = $this->createMock(WordUpdater::class);
        $request = $this->updateRequest();
        $payload = Payload::of($request);

        $command = UpdateWord::createBy($this->id, $payload);
        $wordUpdaterMock->expects($this->once())
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

        $wordManager = $this->createWordManager(
            $this->createStub(WordsProvider::class),
            $this->createStub(WordCreator::class),
            $wordUpdaterMock,
            $this->createStub(WordRemover::class),
        );
        $response = $wordManager->update($this->createStub(AuthenticatedUserInterface::class), $request);

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

        return $request
            ->withAttribute(attribute: 'id', value: '1')
            ->withParsedBody($expectedArray);
    }


    public function testReturnsErrorResponseIfCannotUpdateWordBecauseItAlreadyExistsForNewVersion(): void
    {
        $wordUpdaterMock = $this->createMock(WordUpdater::class);
        $request = $this->updateRequest();
        $payload = Payload::of($request);

        $throwable = new RuntimeException('ooops', ResponseCode::conflict->value);

        $command = UpdateWord::createBy($this->id, $payload);
        $wordUpdaterMock->expects($this->once())
            ->method('handle')
            ->with($command)
            ->willThrowException($throwable);

        $this->logger->expects($this->never())
            ->method('info');
        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedResponse = ErrorResponse::conflict($this->url, $throwable);

        $wordManager = $this->createWordManager(
            $this->createStub(WordsProvider::class),
            $this->createStub(WordCreator::class),
            $wordUpdaterMock,
            $this->createStub(WordRemover::class),
        );
        $response = $wordManager->update($this->createStub(AuthenticatedUserInterface::class), $request);

        $this->assertions($expectedResponse, $response);
    }


    public function testReturnsErrorResponseOnThrownExceptionIfCannotUpdateWordBecauseItIsNotFound(): void
    {
        $wordUpdaterMock = $this->createMock(WordUpdater::class);
        $request = $this->updateRequest();
        $payload = Payload::of($request);

        $throwable = new RuntimeException('ooops', ResponseCode::notFound->value);

        $command = UpdateWord::createBy($this->id, $payload);
        $wordUpdaterMock->expects($this->once())
            ->method('handle')
            ->with($command)
            ->willThrowException($throwable);

        $this->logger->expects($this->never())
            ->method('info');
        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedResponse = ErrorResponse::notFound($this->url, $throwable);

        $wordManager = $this->createWordManager(
            $this->createStub(WordsProvider::class),
            $this->createStub(WordCreator::class),
            $wordUpdaterMock,
            $this->createStub(WordRemover::class),
        );
        $response = $wordManager->update($this->createStub(AuthenticatedUserInterface::class), $request);

        $this->assertions($expectedResponse, $response);
    }


    public function testReturnsErrorResponseOnThrownExceptionIfCannotUpdateDueToSomeOtherError(): void
    {
        $wordUpdaterMock = $this->createMock(WordUpdater::class);
        $request = $this->updateRequest();
        $payload = Payload::of($request);

        $throwable = new RuntimeException('ooops', ResponseCode::serverError->value);

        $command = UpdateWord::createBy($this->id, $payload);
        $wordUpdaterMock->expects($this->once())
            ->method('handle')
            ->with($command)
            ->willThrowException($throwable);

        $this->logger->expects($this->never())
            ->method('info');
        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedResponse = ErrorResponse::serverError($this->url, $throwable);

        $wordManager = $this->createWordManager(
            $this->createStub(WordsProvider::class),
            $this->createStub(WordCreator::class),
            $wordUpdaterMock,
            $this->createStub(WordRemover::class),
        );
        $response = $wordManager->update($this->createStub(AuthenticatedUserInterface::class), $request);

        $this->assertions($expectedResponse, $response);
    }


    public function testCanDelete(): void
    {
        $wordRemoverMock = $this->createMock(WordRemover::class);
        $request = $this->deleteRequest();

        $command = DeleteWord::createBy($this->id);
        $wordRemoverMock->expects($this->once())
            ->method('handle')
            ->with($command);

        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                LogMessage::fromString('Removed Id: 1')
            );

        $json = Json::fromString('{"message":"Removed word with id: 1"}');
        $expectedResponse = SuccessResponse::deletedRecord($this->url, $json);

        $wordManager = $this->createWordManager(
            $this->createStub(WordsProvider::class),
            $this->createStub(WordCreator::class),
            $this->createStub(WordUpdater::class),
            $wordRemoverMock,
        );
        $response = $wordManager->delete($this->createStub(AuthenticatedUserInterface::class), $request);

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


    public function testReturnsErrorResponseOnThrownExceptionIfCannotDeleteWordBecauseItIsNotFound(): void
    {
        $wordRemoverMock = $this->createMock(WordRemover::class);
        $request = $this->deleteRequest();

        $throwable = new RuntimeException('ooops', ResponseCode::notFound->value);

        $command = DeleteWord::createBy($this->id);
        $wordRemoverMock->expects($this->once())
            ->method('handle')
            ->with($command)
            ->willThrowException($throwable);

        $this->logger->expects($this->never())
            ->method('info');
        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedResponse = ErrorResponse::notFound($this->url, $throwable);

        $wordManager = $this->createWordManager(
            $this->createStub(WordsProvider::class),
            $this->createStub(WordCreator::class),
            $this->createStub(WordUpdater::class),
            $wordRemoverMock,
        );
        $response = $wordManager->delete($this->createStub(AuthenticatedUserInterface::class), $request);

        $this->assertions($expectedResponse, $response);
    }


    public function testReturnsErrorResponseOnThrownExceptionIfCannotDeleteDueToSomeOtherError(): void
    {
        $wordRemoverMock = $this->createMock(WordRemover::class);
        $request = $this->deleteRequest();

        $throwable = new RuntimeException('ooops', ResponseCode::serverError->value);

        $command = DeleteWord::createBy($this->id);
        $wordRemoverMock->expects($this->once())
            ->method('handle')
            ->with($command)
            ->willThrowException($throwable);

        $this->logger->expects($this->never())
            ->method('info');
        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedResponse = ErrorResponse::serverError($this->url, $throwable);

        $wordManager = $this->createWordManager(
            $this->createStub(WordsProvider::class),
            $this->createStub(WordCreator::class),
            $this->createStub(WordUpdater::class),
            $wordRemoverMock,
        );
        $response = $wordManager->delete($this->createStub(AuthenticatedUserInterface::class), $request);

        $this->assertions($expectedResponse, $response);
    }
}
