<?php

declare(strict_types=1);

namespace norsk\api\trainer\words;

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
use norsk\api\tests\provider\WordProvider;
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

#[CoversClass(WordTrainer::class)]
class WordTrainerTest extends TestCase
{
    private Logger|MockObject $loggerMock;

    private MockObject|RandomGenerator $randomGeneratorMock;

    private WordReader|MockObject $readerMock;

    private TrainingWriter|MockObject $writerMock;

    private TrainingWord $word;

    private Id $id;

    private Url $url;


    protected function setUp(): void
    {
        $this->url = Url::by('http://url');
        $this->loggerMock = $this->createMock(Logger::class);
        $this->randomGeneratorMock = $this->createMock(RandomGenerator::class);
        $this->readerMock = $this->createMock(WordReader::class);
        $this->writerMock = $this->createMock(TrainingWriter::class);
        $this->id = Id::by(3);
    }


    public function testCanGetWordToTrain(): void
    {
        $body = Json::fromString(WordProvider::managedWordArchipelagoAsJsonString());
        $expectedResponse = VocabularyToTrainResponse::create($this->url, $body);

        $words = $this->getWords();

        $this->readerMock->expects($this->once())
            ->method('getAllWordsFor')
            ->with(UserName::by('someUsername'))
            ->willReturn($words);

        $this->randomGeneratorMock->expects($this->once())
            ->method('pickFrom')
            ->willReturn($this->word);

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with(
                LogMessage::fromString(
                    'Word to train: '
                    . WordProvider::managedWordArchipelagoAsJsonString() . ' '
                    . 'for user: someUsername'
                )
            );

        $_SESSION['user'] = 'someUsername';
        $trainer = new WordTrainer(
            $this->loggerMock,
            $this->randomGeneratorMock,
            $this->readerMock,
            $this->writerMock,
            $this->url
        );

        $response = $trainer->getWordToTrain();
        $this->assertions($expectedResponse, $response);
    }


    private function getWords(): Vocabularies
    {
        $this->word = WordProvider::trainingWordArchipelago();
        $words = Vocabularies::create();
        $words->add($this->word);

        return $words;
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
            ->method('getAllWordsFor')
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
        $trainer = new WordTrainer(
            $this->loggerMock,
            $this->randomGeneratorMock,
            $this->readerMock,
            $this->writerMock,
            $this->url
        );

        $response = $trainer->getWordToTrain();
        $this->assertions($expectedResponse, $response);
    }


    public function testCanSaveSuccess(): void
    {
        $expectedResponse = NoContentResponse::vocabularyTrainedSuccessfully($this->url);

        $request = $this->getRequest();

        $this->writerMock->expects($this->once())
            ->method('save')
            ->with(UserName::by('someUsername'), $this->id);

        $this->loggerMock->expects($this->once())
            ->method('info')
            ->with(
                LogMessage::fromString(
                    'Saved successfully trained wordId: 3'
                    . ' for user: someUsername'
                )
            );

        $_SESSION['user'] = 'someUsername';
        $trainer = new WordTrainer(
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
        $expectedArray = WordProvider::managedWordArchipelagoAsArray();

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
            ->willReturn($this->id->asString());

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
            ->with(UserName::by('someUsername'), $this->id)
            ->willThrowException($throwable);

        $this->loggerMock->expects($this->never())
            ->method('info');
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($throwable);

        $_SESSION['user'] = 'someUsername';
        $trainer = new WordTrainer(
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
            ->with(UserName::by('someUsername'), $this->id)
            ->willThrowException($throwable);

        $this->loggerMock->expects($this->never())
            ->method('info');
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($throwable);

        $_SESSION['user'] = 'someUsername';
        $trainer = new WordTrainer(
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
            ->with(UserName::by('someUsername'), $this->id)
            ->willThrowException($throwable);

        $this->loggerMock->expects($this->never())
            ->method('info');
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($throwable);

        $_SESSION['user'] = 'someUsername';
        $trainer = new WordTrainer(
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
