<?php

declare(strict_types=1);

namespace norsk\api\trainer\infrastructure\web\controller;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use norsk\api\infrastructure\logging\Logger;
use norsk\api\infrastructure\logging\LogMessage;
use norsk\api\shared\application\Json;
use norsk\api\shared\domain\Id;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\shared\infrastructure\http\response\responses\ErrorResponse;
use norsk\api\shared\infrastructure\http\response\responses\NoContentResponse;
use norsk\api\shared\infrastructure\http\response\Url;
use norsk\api\tests\provider\WordProvider;
use norsk\api\trainer\application\wordTraining\useCases\GetWordToTrain;
use norsk\api\trainer\application\wordTraining\useCases\SaveTrainedWord;
use norsk\api\trainer\application\wordTraining\WordProgressUpdater;
use norsk\api\trainer\application\wordTraining\WordToTrainProvider;
use norsk\api\trainer\domain\words\TrainingWord;
use norsk\api\trainer\infrastructure\web\responses\VocabularyToTrainResponse;
use norsk\api\user\application\AuthenticatedUserInterface;
use norsk\api\user\domain\valueObjects\UserName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

#[CoversClass(WordTrainer::class)]
class WordTrainerTest extends TestCase
{
    private Logger|MockObject $loggerMock;

    private Id $id;

    private Url $url;

    private Json $body;

    private UserName $userName;

    private TrainingWord|MockObject $trainingWordMock;

    private WordToTrainProvider|MockObject $getWordToTrainHandler;

    private WordProgressUpdater|MockObject $saveTrainedWordHandler;

    private GetWordToTrain $getWordToTrainCommand;

    private SaveTrainedWord $saveTrainedWordCommand;

    private AuthenticatedUserInterface|MockObject $authenticatedUserMock;


    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(Logger::class);

        $this->url = Url::by('http://url');
        $this->id = Id::by(3);
        $this->userName = UserName::by('someUsername');
        $this->body = Json::fromString(WordProvider::managedWordArchipelagoAsJsonString());

        $this->authenticatedUserMock = $this->createMock(AuthenticatedUserInterface::class);
        $this->authenticatedUserMock->expects($this->once())
            ->method('getUserName')
            ->willReturn($this->userName);

        $this->getWordToTrainHandler = $this->createMock(WordToTrainProvider::class);
        $this->getWordToTrainCommand = GetWordToTrain::for($this->userName);

        $this->saveTrainedWordHandler = $this->createMock(WordProgressUpdater::class);
        $this->saveTrainedWordCommand = SaveTrainedWord::for($this->userName, $this->id);

        $this->trainingWordMock = $this->createMock(TrainingWord::class);
        $this->trainingWordMock->method('asJson')
            ->willReturn($this->body);
    }


    public function testCanGetWordToTrain(): void
    {
        $expectedResponse = VocabularyToTrainResponse::create($this->url, $this->body);

        $this->getWordToTrainHandler->expects($this->once())
            ->method('handle')
            ->with($this->getWordToTrainCommand)
            ->willReturn($this->trainingWordMock);

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with(
                LogMessage::fromString(
                    'Word to train: '
                    . WordProvider::managedWordArchipelagoAsJsonString() . ' '
                    . 'for user: someUsername'
                )
            );

        $trainer = new WordTrainer(
            $this->loggerMock,
            $this->getWordToTrainHandler,
            $this->saveTrainedWordHandler,
            $this->url
        );

        $response = $trainer->getWordToTrain($this->authenticatedUserMock);
        $this->assertions($expectedResponse, $response);
    }


    private function assertions(
        Response $expectedResponse,
        ResponseInterface $response
    ): void {
        self::assertSame($expectedResponse->getStatusCode(), $response->getStatusCode());
        self::assertSame($expectedResponse->getBody()->getContents(), $response->getBody()->getContents());
    }


    public function testThrowsExceptionOnErrorWhileTryingToGetWordToTrain(): void
    {
        $throwable = new RuntimeException('ooops');
        $expectedResponse = ErrorResponse::serverError($this->url, $throwable);

        $this->getWordToTrainHandler->expects($this->once())
            ->method('handle')
            ->with($this->getWordToTrainCommand)
            ->willThrowException($throwable);

        $this->loggerMock->expects($this->never())
            ->method('info');
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($throwable);

        $trainer = new WordTrainer(
            $this->loggerMock,
            $this->getWordToTrainHandler,
            $this->saveTrainedWordHandler,
            $this->url
        );

        $response = $trainer->getWordToTrain($this->authenticatedUserMock);
        $this->assertions($expectedResponse, $response);
    }


    public function testCanSaveSuccess(): void
    {
        $expectedResponse = NoContentResponse::vocabularyTrainedSuccessfully($this->url);

        $request = $this->getRequest();

        $this->saveTrainedWordHandler->expects($this->once())
            ->method('handle')
            ->with($this->saveTrainedWordCommand);

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with(
                LogMessage::fromString(
                    'Saved successfully trained wordId: 3'
                    . ' for user: someUsername'
                )
            );

        $trainer = new WordTrainer(
            $this->loggerMock,
            $this->getWordToTrainHandler,
            $this->saveTrainedWordHandler,
            $this->url
        );

        $response = $trainer->saveSuccess($this->authenticatedUserMock, $request);
        $this->assertions($expectedResponse, $response);
    }


    private function getRequest(): ServerRequest
    {
        $request = new ServerRequest(
            method: 'patch',
            uri: 'foo',
            headers: [],
            body: $this->body->asString()
        );

        return $request->withAttribute('id', $this->id->asString());
    }


    public function testReturnsErrorResponseOnMissingParameterWhileTryingToGetWordToTrain(): void
    {
        $throwable = new RuntimeException('ooops', ResponseCode::badRequest->value);
        $expectedResponse = ErrorResponse::badRequest($this->url, $throwable);

        $request = $this->getRequest();

        $this->saveTrainedWordHandler->expects($this->once())
            ->method('handle')
            ->with($this->saveTrainedWordCommand)
            ->willThrowException($throwable);

        $this->loggerMock->expects($this->never())
            ->method('info');
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($throwable);

        $trainer = new WordTrainer(
            $this->loggerMock,
            $this->getWordToTrainHandler,
            $this->saveTrainedWordHandler,
            $this->url
        );

        $response = $trainer->saveSuccess($this->authenticatedUserMock, $request);
        $this->assertions($expectedResponse, $response);
    }


    public function testReturnsErrorResponseIfWordIsNotFoundWhileTryingToGetWordToTrain(): void
    {
        $throwable = new RuntimeException('ooops', ResponseCode::notFound->value);
        $expectedResponse = ErrorResponse::notFound($this->url, $throwable);

        $request = $this->getRequest();

        $this->saveTrainedWordHandler->expects($this->once())
            ->method('handle')
            ->with($this->saveTrainedWordCommand)
            ->willThrowException($throwable);

        $this->loggerMock->expects($this->never())
            ->method('info');
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($throwable);

        $trainer = new WordTrainer(
            $this->loggerMock,
            $this->getWordToTrainHandler,
            $this->saveTrainedWordHandler,
            $this->url
        );

        $response = $trainer->saveSuccess($this->authenticatedUserMock, $request);
        $this->assertions($expectedResponse, $response);
    }


    public function testReturnsErrorResponseOnAnyOtherErrorWhileTryingToGetWordToTrain(): void
    {
        $throwable = new RuntimeException('ooops', ResponseCode::serverError->value);
        $expectedResponse = ErrorResponse::serverError($this->url, $throwable);

        $request = $this->getRequest();

        $this->saveTrainedWordHandler->expects($this->once())
            ->method('handle')
            ->with($this->saveTrainedWordCommand)
            ->willThrowException($throwable);

        $this->loggerMock->expects($this->never())
            ->method('info');
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($throwable);

        $trainer = new WordTrainer(
            $this->loggerMock,
            $this->getWordToTrainHandler,
            $this->saveTrainedWordHandler,
            $this->url
        );

        $response = $trainer->saveSuccess($this->authenticatedUserMock, $request);
        $this->assertions($expectedResponse, $response);
    }
}
