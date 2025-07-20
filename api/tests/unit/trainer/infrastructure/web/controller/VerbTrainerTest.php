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
use norsk\api\tests\provider\VerbProvider;
use norsk\api\trainer\application\verbTraining\useCases\GetVerbToTrain;
use norsk\api\trainer\application\verbTraining\useCases\SaveTrainedVerb;
use norsk\api\trainer\application\verbTraining\VerbProgressUpdater;
use norsk\api\trainer\application\verbTraining\VerbToTrainProvider;
use norsk\api\trainer\domain\verbs\TrainingVerb;
use norsk\api\trainer\infrastructure\web\responses\VocabularyToTrainResponse;
use norsk\api\user\application\AuthenticatedUserInterface;
use norsk\api\user\domain\valueObjects\UserName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

#[CoversClass(VerbTrainer::class)]
class VerbTrainerTest extends TestCase
{
    private Logger|MockObject $loggerMock;

    private Url $url;

    private Id $id;

    private UserName $userName;

    private TrainingVerb|MockObject $trainingVerbMock;

    private VerbToTrainProvider|MockObject $getVerbToTrainHandler;

    private VerbProgressUpdater|MockObject $saveTrainedVerbHandler;

    private GetVerbToTrain $getVerbToTrainCommand;

    private SaveTrainedVerb $saveTrainedVerbCommand;

    private Json $body;

    private AuthenticatedUserInterface|MockObject $authenticatedUserMock;


    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(Logger::class);

        $this->url = Url::by('http://url');
        $this->id = Id::by(1);
        $this->userName = UserName::by('someUsername');
        $this->body = Json::encodeFromArray(VerbProvider::managedVerbToGoAsArray());

        $this->authenticatedUserMock = $this->createMock(AuthenticatedUserInterface::class);
        $this->authenticatedUserMock->expects($this->once())
            ->method('getUserName')
            ->willReturn($this->userName);

        $this->getVerbToTrainHandler = $this->createMock(VerbToTrainProvider::class);
        $this->getVerbToTrainCommand = GetVerbToTrain::for($this->userName);

        $this->saveTrainedVerbHandler = $this->createMock(VerbProgressUpdater::class);
        $this->saveTrainedVerbCommand = SaveTrainedVerb::for($this->userName, $this->id);

        $this->trainingVerbMock = $this->createMock(TrainingVerb::class);
        $this->trainingVerbMock->method('asJson')
            ->willReturn($this->body);
    }


    public function testCanGetVerbToTrain(): void
    {
        $expectedResponse = VocabularyToTrainResponse::create($this->url, $this->body);

        $this->getVerbToTrainHandler->expects($this->once())
            ->method('handle')
            ->with($this->getVerbToTrainCommand)
            ->willReturn($this->trainingVerbMock);

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with(
                LogMessage::fromString(
                    'Verb to train: '
                    . '{"id":1,"german":"gehen","norsk":"g\u00e5","norskPresent":"g\u00e5r",'
                    . '"norskPast":"gikk","norskPastPerfect":"har g\u00e5tt"} '
                    . 'for user: someUsername'
                )
            );

        $trainer = new VerbTrainer(
            $this->loggerMock,
            $this->getVerbToTrainHandler,
            $this->saveTrainedVerbHandler,
            $this->url
        );

        $response = $trainer->getVerbToTrain($this->authenticatedUserMock);
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

        $this->getVerbToTrainHandler->expects($this->once())
            ->method('handle')
            ->with($this->getVerbToTrainCommand)
            ->willThrowException($throwable);

        $this->loggerMock->expects($this->never())
            ->method('info');
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($throwable);

        $trainer = new VerbTrainer(
            $this->loggerMock,
            $this->getVerbToTrainHandler,
            $this->saveTrainedVerbHandler,
            $this->url
        );

        $response = $trainer->getVerbToTrain($this->authenticatedUserMock);
        $this->assertions($expectedResponse, $response);
    }


    public function testCanSaveSuccess(): void
    {
        $expectedResponse = NoContentResponse::vocabularyTrainedSuccessfully($this->url);

        $request = $this->getRequest();

        $this->saveTrainedVerbHandler->expects($this->once())
            ->method('handle')
            ->with($this->saveTrainedVerbCommand);

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with(
                LogMessage::fromString(
                    'Saved successfully trained verbId: 1'
                    . ' for user: someUsername'
                )
            );

        $trainer = new VerbTrainer(
            $this->loggerMock,
            $this->getVerbToTrainHandler,
            $this->saveTrainedVerbHandler,
            $this->url
        );

        $response = $trainer->saveSuccess($this->authenticatedUserMock,$request);
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

        $this->saveTrainedVerbHandler->expects($this->once())
            ->method('handle')
            ->with($this->saveTrainedVerbCommand)
            ->willThrowException($throwable);

        $this->loggerMock->expects($this->never())
            ->method('info');
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($throwable);

        $trainer = new VerbTrainer(
            $this->loggerMock,
            $this->getVerbToTrainHandler,
            $this->saveTrainedVerbHandler,
            $this->url
        );

        $response = $trainer->saveSuccess($this->authenticatedUserMock,$request);
        $this->assertions($expectedResponse, $response);
    }


    public function testReturnsErrorResponseIfWordIsNotFoundWhileTryingToGetWordToTrain(): void
    {
        $throwable = new RuntimeException('ooops', ResponseCode::notFound->value);
        $expectedResponse = ErrorResponse::notFound($this->url, $throwable);

        $request = $this->getRequest();

        $this->saveTrainedVerbHandler->expects($this->once())
            ->method('handle')
            ->with($this->saveTrainedVerbCommand)
            ->willThrowException($throwable);

        $this->loggerMock->expects($this->never())
            ->method('info');
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($throwable);

        $trainer = new VerbTrainer(
            $this->loggerMock,
            $this->getVerbToTrainHandler,
            $this->saveTrainedVerbHandler,
            $this->url
        );

        $response = $trainer->saveSuccess($this->authenticatedUserMock,$request);
        $this->assertions($expectedResponse, $response);
    }


    public function testReturnsErrorResponseOnAnyOtherErrorWhileTryingToGetWordToTrain(): void
    {
        $throwable = new RuntimeException('ooops', ResponseCode::serverError->value);
        $expectedResponse = ErrorResponse::serverError($this->url, $throwable);

        $request = $this->getRequest();

        $this->saveTrainedVerbHandler->expects($this->once())
            ->method('handle')
            ->with($this->saveTrainedVerbCommand)
            ->willThrowException($throwable);

        $this->loggerMock->expects($this->never())
            ->method('info');
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($throwable);

        $trainer = new VerbTrainer(
            $this->loggerMock,
            $this->getVerbToTrainHandler,
            $this->saveTrainedVerbHandler,
            $this->url
        );

        $response = $trainer->saveSuccess($this->authenticatedUserMock,$request);
        $this->assertions($expectedResponse, $response);
    }
}
