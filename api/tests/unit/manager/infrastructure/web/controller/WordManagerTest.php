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

    private WordsProvider|MockObject $wordsProviderMock;

    private WordCreator|MockObject $wordCreatorMock;

    private WordUpdater|MockObject $wordUpdaterMock;

    private WordRemover|MockObject $wordRemoverMock;

    private Id $id;

    private UserName $userName;

    private AuthenticatedUserInterface|MockObject $authenticatedUserMock;


    protected function setUp(): void
    {
        $this->url = Url::by('http://url');
        $this->logger = $this->createMock(Logger::class);
        $this->id = Id::by(1);
        $this->userName = UserName::by('someBody');

        $this->authenticatedUserMock = $this->createMock(AuthenticatedUserInterface::class);

        $this->wordsProviderMock = $this->createMock(WordsProvider::class);
        $this->wordCreatorMock = $this->createMock(WordCreator::class);
        $this->wordUpdaterMock = $this->createMock(WordUpdater::class);
        $this->wordRemoverMock = $this->createMock(WordRemover::class);
    }


    public function testCanGetAllWords(): void
    {
        $wordManager = new WordManager(
            $this->logger,
            $this->wordsProviderMock,
            $this->wordCreatorMock,
            $this->wordUpdaterMock,
            $this->wordRemoverMock,
            $this->url
        );

        $this->authenticatedUserMock->expects($this->once())
            ->method('getUserName')
            ->willReturn($this->userName);

        $command = GetAllWords::create();
        $this->wordsProviderMock->expects($this->once())
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

        $response = $wordManager->getAllWords($this->authenticatedUserMock);
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
        $this->wordsProviderMock->expects($this->once())
            ->method('handle')
            ->willThrowException($throwable);

        $this->logger->expects($this->never())
            ->method('info');
        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedWords = ErrorResponse::serverError($this->url, $throwable);

        $wordManager = new WordManager(
            $this->logger,
            $this->wordsProviderMock,
            $this->wordCreatorMock,
            $this->wordUpdaterMock,
            $this->wordRemoverMock,
            $this->url
        );
        $response = $wordManager->getAllWords($this->authenticatedUserMock);

        $this->assertions($expectedWords, $response);
    }


    public function testCanCreateWord(): void
    {
        $requestMock = $this->createRequest();

        $payload = Payload::of($requestMock);
        $command = CreateWord::createBy($payload);
        $this->wordCreatorMock->expects($this->once())
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

        $wordManager = new WordManager(
            $this->logger,
            $this->wordsProviderMock,
            $this->wordCreatorMock,
            $this->wordUpdaterMock,
            $this->wordRemoverMock,
            $this->url
        );
        $response = $wordManager->createWord($this->authenticatedUserMock, $requestMock);

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
        $this->wordCreatorMock->expects($this->once())
            ->method('handle')
            ->willThrowException($throwable);

        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);
        $this->logger->expects($this->never())
            ->method('info');

        $expectedResponse = ErrorResponse::conflict($this->url, $throwable);

        $wordManager = new WordManager(
            $this->logger,
            $this->wordsProviderMock,
            $this->wordCreatorMock,
            $this->wordUpdaterMock,
            $this->wordRemoverMock,
            $this->url
        );
        $response = $wordManager->createWord($this->authenticatedUserMock, $requestMock);

        $this->assertions($expectedResponse, $response);
    }


    public function testReturnsErrorResponseOnThrownExceptionIfCannotCreateWordDueToSomeOtherError(): void
    {
        $requestMock = $this->createRequest();

        $throwable = new RuntimeException('ooops', ResponseCode::badRequest->value);
        $this->wordCreatorMock->expects($this->once())
            ->method('handle')
            ->willThrowException($throwable);

        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);
        $this->logger->expects($this->never())
            ->method('info');

        $expectedResponse = ErrorResponse::serverError($this->url, $throwable);

        $wordManager = new WordManager(
            $this->logger,
            $this->wordsProviderMock,
            $this->wordCreatorMock,
            $this->wordUpdaterMock,
            $this->wordRemoverMock,
            $this->url
        );
        $response = $wordManager->createWord($this->authenticatedUserMock, $requestMock);

        $this->assertions($expectedResponse, $response);
    }


    public function testCanUpdate(): void
    {
        $request = $this->updateRequest();
        $payload = Payload::of($request);

        $command = UpdateWord::createBy($this->id, $payload);
        $this->wordUpdaterMock->expects($this->once())
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

        $wordManager = new WordManager(
            $this->logger,
            $this->wordsProviderMock,
            $this->wordCreatorMock,
            $this->wordUpdaterMock,
            $this->wordRemoverMock,
            $this->url
        );
        $response = $wordManager->update($this->authenticatedUserMock, $request);

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
        $request = $this->updateRequest();
        $payload = Payload::of($request);

        $throwable = new RuntimeException('ooops', ResponseCode::conflict->value);

        $command = UpdateWord::createBy($this->id, $payload);
        $this->wordUpdaterMock->expects($this->once())
            ->method('handle')
            ->with($command)
            ->willThrowException($throwable);

        $this->logger->expects($this->never())
            ->method('info');
        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedResponse = ErrorResponse::conflict($this->url, $throwable);

        $wordManager = new WordManager(
            $this->logger,
            $this->wordsProviderMock,
            $this->wordCreatorMock,
            $this->wordUpdaterMock,
            $this->wordRemoverMock,
            $this->url
        );
        $response = $wordManager->update($this->authenticatedUserMock, $request);

        $this->assertions($expectedResponse, $response);
    }


    public function testReturnsErrorResponseOnThrownExceptionIfCannotUpdateWordBecauseItIsNotFound(): void
    {
        $request = $this->updateRequest();
        $payload = Payload::of($request);

        $throwable = new RuntimeException('ooops', ResponseCode::notFound->value);

        $command = UpdateWord::createBy($this->id, $payload);
        $this->wordUpdaterMock->expects($this->once())
            ->method('handle')
            ->with($command)
            ->willThrowException($throwable);

        $this->logger->expects($this->never())
            ->method('info');
        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedResponse = ErrorResponse::notFound($this->url, $throwable);

        $wordManager = new WordManager(
            $this->logger,
            $this->wordsProviderMock,
            $this->wordCreatorMock,
            $this->wordUpdaterMock,
            $this->wordRemoverMock,
            $this->url
        );
        $response = $wordManager->update($this->authenticatedUserMock, $request);

        $this->assertions($expectedResponse, $response);
    }


    public function testReturnsErrorResponseOnThrownExceptionIfCannotUpdateDueToSomeOtherError(): void
    {
        $request = $this->updateRequest();
        $payload = Payload::of($request);

        $throwable = new RuntimeException('ooops', ResponseCode::serverError->value);

        $command = UpdateWord::createBy($this->id, $payload);
        $this->wordUpdaterMock->expects($this->once())
            ->method('handle')
            ->with($command)
            ->willThrowException($throwable);

        $this->logger->expects($this->never())
            ->method('info');
        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedResponse = ErrorResponse::serverError($this->url, $throwable);

        $wordManager = new WordManager(
            $this->logger,
            $this->wordsProviderMock,
            $this->wordCreatorMock,
            $this->wordUpdaterMock,
            $this->wordRemoverMock,
            $this->url
        );
        $response = $wordManager->update($this->authenticatedUserMock, $request);

        $this->assertions($expectedResponse, $response);
    }


    public function testCanDelete(): void
    {
        $request = $this->deleteRequest();

        $command = DeleteWord::createBy($this->id);
        $this->wordRemoverMock->expects($this->once())
            ->method('handle')
            ->with($command);

        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                LogMessage::fromString('Removed Id: 1')
            );

        $json = Json::fromString('{"message":"Removed word with id: 1"}');
        $expectedResponse = SuccessResponse::deletedRecord($this->url, $json);

        $wordManager = new WordManager(
            $this->logger,
            $this->wordsProviderMock,
            $this->wordCreatorMock,
            $this->wordUpdaterMock,
            $this->wordRemoverMock,
            $this->url
        );
        $response = $wordManager->delete($this->authenticatedUserMock, $request);

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
        $request = $this->deleteRequest();

        $throwable = new RuntimeException('ooops', ResponseCode::notFound->value);

        $command = DeleteWord::createBy($this->id);
        $this->wordRemoverMock->expects($this->once())
            ->method('handle')
            ->with($command)
            ->willThrowException($throwable);

        $this->logger->expects($this->never())
            ->method('info');
        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedResponse = ErrorResponse::notFound($this->url, $throwable);

        $wordManager = new WordManager(
            $this->logger,
            $this->wordsProviderMock,
            $this->wordCreatorMock,
            $this->wordUpdaterMock,
            $this->wordRemoverMock,
            $this->url
        );
        $response = $wordManager->delete($this->authenticatedUserMock, $request);

        $this->assertions($expectedResponse, $response);
    }


    public function testReturnsErrorResponseOnThrownExceptionIfCannotDeleteDueToSomeOtherError(): void
    {
        $request = $this->deleteRequest();

        $throwable = new RuntimeException('ooops', ResponseCode::serverError->value);

        $command = DeleteWord::createBy($this->id);
        $this->wordRemoverMock->expects($this->once())
            ->method('handle')
            ->with($command)
            ->willThrowException($throwable);

        $this->logger->expects($this->never())
            ->method('info');
        $this->logger->expects($this->once())
            ->method('error')
            ->with($throwable);

        $expectedResponse = ErrorResponse::serverError($this->url, $throwable);

        $wordManager = new WordManager(
            $this->logger,
            $this->wordsProviderMock,
            $this->wordCreatorMock,
            $this->wordUpdaterMock,
            $this->wordRemoverMock,
            $this->url
        );
        $response = $wordManager->delete($this->authenticatedUserMock, $request);

        $this->assertions($expectedResponse, $response);
    }
}
