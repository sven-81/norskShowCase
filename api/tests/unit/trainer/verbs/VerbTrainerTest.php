<?php

declare(strict_types=1);

namespace norsk\api\trainer\verbs;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use norsk\api\app\logging\Logger;
use norsk\api\app\logging\LogMessage;
use norsk\api\app\response\ResponseCode;
use norsk\api\app\response\Url;
use norsk\api\shared\Id;
use norsk\api\shared\Json;
use norsk\api\shared\responses\ErrorResponse;
use norsk\api\shared\responses\NoContentResponse;
use norsk\api\shared\Vocabularies;
use norsk\api\tests\provider\VerbProvider;
use norsk\api\trainer\RandomGenerator;
use norsk\api\trainer\responses\VocabularyToTrainResponse;
use norsk\api\trainer\TrainingWriter;
use norsk\api\user\UserName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Slim\Routing\Route;
use Slim\Routing\RouteParser;
use Slim\Routing\RoutingResults;

#[CoversClass(VerbTrainer::class)]
class VerbTrainerTest extends TestCase
{
    private Logger|MockObject $loggerMock;

    private MockObject|RandomGenerator $randomGeneratorMock;

    private VerbReader|MockObject $readerMock;

    private TrainingWriter|MockObject $writerMock;

    private TrainingVerb $verb;

    private Url $url;


    protected function setUp(): void
    {
        $this->url = Url::by('http://url');
        $this->loggerMock = $this->createMock(Logger::class);
        $this->randomGeneratorMock = $this->createMock(RandomGenerator::class);
        $this->readerMock = $this->createMock(VerbReader::class);
        $this->writerMock = $this->createMock(TrainingWriter::class);
    }


    public function testCanGetVerbToTrain(): void
    {
        $body = Json::fromString(
            '{"id":1,"german":"gehen","norsk":"g\u00e5","norskPresent":"g\u00e5r",'
            . '"norskPast":"gikk","norskPastPerfect":"har g\u00e5tt"}'
        );
        $expectedResponse = VocabularyToTrainResponse::create($this->url, $body);

        $verbs = $this->getVerbs();

        $this->readerMock->expects($this->once())
            ->method('getAllVerbsFor')
            ->with(UserName::by('someUsername'))
            ->willReturn($verbs);

        $this->randomGeneratorMock->expects($this->once())
            ->method('pickFrom')
            ->willReturn($this->verb);

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

        $_SESSION['user'] = 'someUsername';
        $trainer = new VerbTrainer(
            $this->loggerMock,
            $this->randomGeneratorMock,
            $this->readerMock,
            $this->writerMock,
            $this->url
        );

        $response = $trainer->getVerbToTrain();
        $this->assertions($expectedResponse, $response);
    }


    private function getVerbs(): Vocabularies
    {
        $this->verb = VerbProvider::trainingVerbToGo();
        $verbs = Vocabularies::create();
        $verbs->add($this->verb);

        return $verbs;
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

        $this->readerMock->expects($this->once())
            ->method('getAllVerbsFor')
            ->with(UserName::by('someUsername'))
            ->willThrowException($throwable);

        $this->randomGeneratorMock->expects($this->never())
            ->method('pickFrom');

        $this->loggerMock->expects($this->never())
            ->method('info');
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($throwable);

        $_SESSION['user'] = 'someUsername';
        $trainer = new VerbTrainer(
            $this->loggerMock,
            $this->randomGeneratorMock,
            $this->readerMock,
            $this->writerMock,
            $this->url
        );

        $response = $trainer->getVerbToTrain();
        $this->assertions($expectedResponse, $response);
    }


    public function testCanSaveSuccess(): void
    {
        $expectedResponse = NoContentResponse::vocabularyTrainedSuccessfully($this->url);

        $request = $this->getRequest();

        $this->writerMock->expects($this->once())
            ->method('save')
            ->with(UserName::by('someUsername'), Id::by(1));

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with(
                LogMessage::fromString(
                    'Saved successfully trained verbId: 1'
                    . ' for user: someUsername'
                )
            );

        $_SESSION['user'] = 'someUsername';
        $trainer = new VerbTrainer(
            $this->loggerMock,
            $this->randomGeneratorMock,
            $this->readerMock,
            $this->writerMock,
            $this->url
        );

        $response = $trainer->saveSuccess($request);
        $this->assertions($expectedResponse, $response);
    }


    private function getRequest(): ServerRequest
    {
        $expectedArray = VerbProvider::managedVerbToGoAsArray();

        $request = new ServerRequest(
            method: 'patch',
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

        return $request->withAttribute('__route__', $route);
    }


    public function testReturnsErrorResponseOnMissingParameterWhileTryingToGetWordToTrain(): void
    {
        $throwable = new RuntimeException('ooops', ResponseCode::badRequest->value);
        $expectedResponse = ErrorResponse::badRequest($this->url, $throwable);

        $request = $this->getRequest();

        $this->writerMock->expects($this->once())
            ->method('save')
            ->with(UserName::by('someUsername'), Id::by(1))
            ->willThrowException($throwable);

        $this->loggerMock->expects($this->never())
            ->method('info');
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($throwable);

        $_SESSION['user'] = 'someUsername';
        $trainer = new VerbTrainer(
            $this->loggerMock,
            $this->randomGeneratorMock,
            $this->readerMock,
            $this->writerMock,
            $this->url
        );

        $response = $trainer->saveSuccess($request);
        $this->assertions($expectedResponse, $response);
    }


    public function testReturnsErrorResponseIfWordIsNotFoundWhileTryingToGetWordToTrain(): void
    {
        $throwable = new RuntimeException('ooops', ResponseCode::notFound->value);
        $expectedResponse = ErrorResponse::notFound($this->url, $throwable);

        $request = $this->getRequest();

        $this->writerMock->expects($this->once())
            ->method('save')
            ->with(UserName::by('someUsername'), Id::by(1))
            ->willThrowException($throwable);

        $this->loggerMock->expects($this->never())
            ->method('info');
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($throwable);

        $_SESSION['user'] = 'someUsername';
        $trainer = new VerbTrainer(
            $this->loggerMock,
            $this->randomGeneratorMock,
            $this->readerMock,
            $this->writerMock,
            $this->url
        );

        $response = $trainer->saveSuccess($request);
        $this->assertions($expectedResponse, $response);
    }


    public function testReturnsErrorResponseOnAnyOtherErrorWhileTryingToGetWordToTrain(): void
    {
        $throwable = new RuntimeException('ooops', ResponseCode::serverError->value);
        $expectedResponse = ErrorResponse::serverError($this->url, $throwable);

        $request = $this->getRequest();

        $this->writerMock->expects($this->once())
            ->method('save')
            ->with(UserName::by('someUsername'), Id::by(1))
            ->willThrowException($throwable);

        $this->loggerMock->expects($this->never())
            ->method('info');
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($throwable);

        $_SESSION['user'] = 'someUsername';
        $trainer = new VerbTrainer(
            $this->loggerMock,
            $this->randomGeneratorMock,
            $this->readerMock,
            $this->writerMock,
            $this->url
        );

        $response = $trainer->saveSuccess($request);
        $this->assertions($expectedResponse, $response);
    }


    protected function tearDown(): void
    {
        unset($_SESSION);
    }
}
