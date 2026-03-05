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

    private TrainingWord $trainingWordStub;

    private GetWordToTrain $getWordToTrainCommand;

    private SaveTrainedWord $saveTrainedWordCommand;


    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(Logger::class);

        $this->url = Url::by('http://url');
        $this->id = Id::by(3);
        $this->userName = UserName::by('someUsername');
        $this->body = Json::fromString(WordProvider::managedWordArchipelagoAsJsonString());

        $this->getWordToTrainCommand = GetWordToTrain::for($this->userName);
        $this->saveTrainedWordCommand = SaveTrainedWord::for($this->userName, $this->id);

        $this->trainingWordStub = $this->createStub(TrainingWord::class);
        $this->trainingWordStub->method('asJson')
            ->willReturn($this->body);
    }


    private function createAuthenticatedUserMock(): AuthenticatedUserInterface&MockObject
    {
        $mock = $this->createMock(AuthenticatedUserInterface::class);
        $mock->expects($this->once())
            ->method('getUserName')
            ->willReturn($this->userName);

        return $mock;
    }


    private function createTrainer(
        WordToTrainProvider $wordToTrainProvider,
        WordProgressUpdater $wordProgressUpdater,
    ): WordTrainer {
        return new WordTrainer(
            $this->loggerMock,
            $wordToTrainProvider,
            $wordProgressUpdater,
            $this->url
        );
    }


    public function testCanGetWordToTrain(): void
    {
        $getWordToTrainMock = $this->createMock(WordToTrainProvider::class);
        $expectedResponse = VocabularyToTrainResponse::create($this->url, $this->body);

        $getWordToTrainMock->expects($this->once())
            ->method('handle')
            ->with($this->getWordToTrainCommand)
            ->willReturn($this->trainingWordStub);

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with(
                LogMessage::fromString(
                    'Word to train: '
                    . WordProvider::managedWordArchipelagoAsJsonString() . ' '
                    . 'for user: someUsername'
                )
            );

        $trainer = $this->createTrainer($getWordToTrainMock, $this->createStub(WordProgressUpdater::class));
        $response = $trainer->getWordToTrain($this->createAuthenticatedUserMock());
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
        $getWordToTrainMock = $this->createMock(WordToTrainProvider::class);
        $throwable = new RuntimeException('ooops');
        $expectedResponse = ErrorResponse::serverError($this->url, $throwable);

        $getWordToTrainMock->expects($this->once())
            ->method('handle')
            ->with($this->getWordToTrainCommand)
            ->willThrowException($throwable);

        $this->loggerMock->expects($this->never())
            ->method('info');
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($throwable);

        $trainer = $this->createTrainer($getWordToTrainMock, $this->createStub(WordProgressUpdater::class));
        $response = $trainer->getWordToTrain($this->createAuthenticatedUserMock());
        $this->assertions($expectedResponse, $response);
    }


    public function testCanSaveSuccess(): void
    {
        $saveTrainedWordMock = $this->createMock(WordProgressUpdater::class);
        $expectedResponse = NoContentResponse::vocabularyTrainedSuccessfully($this->url);

        $request = $this->getRequest();

        $saveTrainedWordMock->expects($this->once())
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

        $trainer = $this->createTrainer($this->createStub(WordToTrainProvider::class), $saveTrainedWordMock);
        $response = $trainer->saveSuccess($this->createAuthenticatedUserMock(), $request);
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
        $saveTrainedWordMock = $this->createMock(WordProgressUpdater::class);
        $throwable = new RuntimeException('ooops', ResponseCode::badRequest->value);
        $expectedResponse = ErrorResponse::badRequest($this->url, $throwable);

        $request = $this->getRequest();

        $saveTrainedWordMock->expects($this->once())
            ->method('handle')
            ->with($this->saveTrainedWordCommand)
            ->willThrowException($throwable);

        $this->loggerMock->expects($this->never())
            ->method('info');
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($throwable);

        $trainer = $this->createTrainer($this->createStub(WordToTrainProvider::class), $saveTrainedWordMock);
        $response = $trainer->saveSuccess($this->createAuthenticatedUserMock(), $request);
        $this->assertions($expectedResponse, $response);
    }


    public function testReturnsErrorResponseIfWordIsNotFoundWhileTryingToGetWordToTrain(): void
    {
        $saveTrainedWordMock = $this->createMock(WordProgressUpdater::class);
        $throwable = new RuntimeException('ooops', ResponseCode::notFound->value);
        $expectedResponse = ErrorResponse::notFound($this->url, $throwable);

        $request = $this->getRequest();

        $saveTrainedWordMock->expects($this->once())
            ->method('handle')
            ->with($this->saveTrainedWordCommand)
            ->willThrowException($throwable);

        $this->loggerMock->expects($this->never())
            ->method('info');
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($throwable);

        $trainer = $this->createTrainer($this->createStub(WordToTrainProvider::class), $saveTrainedWordMock);
        $response = $trainer->saveSuccess($this->createAuthenticatedUserMock(), $request);
        $this->assertions($expectedResponse, $response);
    }


    public function testReturnsErrorResponseOnAnyOtherErrorWhileTryingToGetWordToTrain(): void
    {
        $saveTrainedWordMock = $this->createMock(WordProgressUpdater::class);
        $throwable = new RuntimeException('ooops', ResponseCode::serverError->value);
        $expectedResponse = ErrorResponse::serverError($this->url, $throwable);

        $request = $this->getRequest();

        $saveTrainedWordMock->expects($this->once())
            ->method('handle')
            ->with($this->saveTrainedWordCommand)
            ->willThrowException($throwable);

        $this->loggerMock->expects($this->never())
            ->method('info');
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($throwable);

        $trainer = $this->createTrainer($this->createStub(WordToTrainProvider::class), $saveTrainedWordMock);
        $response = $trainer->saveSuccess($this->createAuthenticatedUserMock(), $request);
        $this->assertions($expectedResponse, $response);
    }
}
