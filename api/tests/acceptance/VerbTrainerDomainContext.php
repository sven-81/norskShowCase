<?php

declare(strict_types=1);

namespace norsk\api\tests\acceptance;

use Behat\Behat\Context\Context;
use Behat\Hook\AfterScenario;
use Behat\Hook\BeforeScenario;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest as Request;
use norsk\api\helperTools\Removable;
use norsk\api\infrastructure\config\AppConfig;
use norsk\api\infrastructure\config\DbConfig;
use norsk\api\infrastructure\config\Path;
use norsk\api\infrastructure\logging\Logger;
use norsk\api\infrastructure\persistence\DbConnection;
use norsk\api\infrastructure\persistence\GenericSqlStatement;
use norsk\api\infrastructure\persistence\MysqliWrapper;
use norsk\api\infrastructure\persistence\Parameters;
use norsk\api\infrastructure\persistence\TableName;
use norsk\api\shared\application\Json;
use norsk\api\shared\infrastructure\http\response\Url;
use norsk\api\tests\provider\JwtUserProvider;
use norsk\api\tests\provider\TestHeader;
use norsk\api\tests\stubs\VirtualTestDatabase;
use norsk\api\trainer\infrastructure\TrainerFactory;
use norsk\api\trainer\infrastructure\web\controller\VerbTrainer;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;

class VerbTrainerDomainContext implements Context
{
    use Removable;
    use TestHeader;

    private VerbTrainer $verbTrainer;

    private TableName $usersTable;

    private VirtualTestDatabase $integrationDatabase;

    private array $requestHeaders;

    private array $responseHeaders;

    private string $uri;

    private ResponseInterface $response;

    private Request $request;

    private string $patchMethod;

    private TableName $verbsTable;

    private TableName $verbsSuccessCounterToUsersTable;

    private Path $logPath;

    private DbConfig $dbConfig;

    private Url $url;


    public function __construct()
    {
        $this->dbConfig = DbConfig::fromPath(
            Path::fromString(__DIR__ . '/resources/configs/mySqlConfig.ini')
        );
        $appConfig = AppConfig::fromPath(
            Path::fromString(__DIR__ . '/resources/configs/appConfig.ini')
        );

        $mysqli = new MysqliWrapper();
        $database = new DbConnection($mysqli, $this->dbConfig);
        $this->logPath = $appConfig->getLogPath();
        $logger = Logger::create($this->logPath);

        $this->url = Url::by('http://foo');

        $context = new TrainerFactory($logger, $database, $appConfig);
        $this->verbTrainer = $context->verbTrainer();
    }


    #[BeforeScenario]
    public function before(): void
    {
        $this->prepareTestDatabase();

        $this->requestHeaders = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . file_get_contents(__DIR__ . '/resources/jwt/heinz.manager.jwt'),
            'tokenType' => 'Bearer',
            'expiresIn' => 7200,
        ];

        $this->responseHeaders = $this->getTestHeaderAsHeaders($this->url);
        $this->patchMethod = 'PATCH';
        $this->uri = 'localhost:9999/api/train/verbs';

        session_start();
    }


    private function prepareTestDatabase(): void
    {
        $this->usersTable = TableName::users;
        $this->integrationDatabase = VirtualTestDatabase::create($this->dbConfig);
        $initialUserEntries = file_get_contents(__DIR__ . '/resources/usersSql/initial.sql');
        $this->integrationDatabase->insertInitialEntryToAvoidFailing($initialUserEntries);

        $this->verbsTable = TableName::verbs;
        $initialVerbsEntries = file_get_contents(__DIR__ . '/resources/verbsSql/initial.sql');
        $this->integrationDatabase->insertInitialEntryToAvoidFailing($initialVerbsEntries);
        $this->integrationDatabase->waitForDatabase();

        $this->verbsSuccessCounterToUsersTable = TableName::verbsSuccessCounterToUsers;
        $initialVerbsToUserEntries = file_get_contents(__DIR__ . '/resources/verbsSql/verbToUser.sql');
        $this->integrationDatabase->insertInitialEntryToAvoidFailing($initialVerbsToUserEntries);
    }


    #[Given('there is a user with the username :heinz')]
    public function thereIsAUserWithTheUsername(string $heinz): void
    {
        $initialUser = GenericSqlStatement::create(
            'SELECT username as ' . $heinz . ' '
            . 'FROM ' . $this->usersTable->value . ' '
            . 'WHERE username = ?;'
        );

        $params = Parameters::init();
        $params->addString($heinz);

        $result = $this->integrationDatabase->select($initialUser, $params);

        Assert::assertCount(1, $result, 'found ' . $heinz . ' as username in database');
    }


    #[Given(':heinz is an :active user')]
    public function getActivityForUser(string $heinz, string $activity): void
    {
        if ($activity === 'active') {
            $active = 1;
        } else {
            $active = 0;
        }

        $initialUser = GenericSqlStatement::create(
            'SELECT username as ' . $heinz . ' '
            . 'FROM ' . $this->usersTable->value . ' '
            . 'WHERE username = ? '
            . 'AND active = ?;'
        );

        $params = Parameters::init();
        $params->addString($heinz);
        $params->addInt($active);

        $result = $this->integrationDatabase->select($initialUser, $params);
        $_SESSION['user'] = $heinz;

        Assert::assertCount(1, $result, 'found ' . $heinz . ' as active username in database');
    }


    #[When(':someone likes to train a verb')]
    public function iLikeToTrainAVerb(string $name): void
    {
        $this->response = $this->verbTrainer->getVerbToTrain(JwtUserProvider::getUser($name));
    }


    #[Then(':someone should get a random verb to train')]
    public function SomeoneShouldGetARandomVerbToTrain(string $name): void
    {
        $this->response = $this->verbTrainer->getVerbToTrain(JwtUserProvider::getUser($name));

        $expectedResponseBody = __DIR__ . '/resources/responses/randomVerb.json';
        $expectedResponse = new Response(
            200,
            $this->responseHeaders,
            file_get_contents($expectedResponseBody)
        );

        Assert::assertEquals(
            $expectedResponse->getStatusCode(),
            $this->response->getStatusCode(),
            'status codes match'
        );
        Assert::assertEquals(
            $expectedResponse->getHeaders(),
            $this->response->getHeaders(),
            'headers match'
        );

        $contents = $this->response->getBody()->getContents();
        $jsonArray = Json::fromString($contents)->asDecodedJson();

        Assert::assertArrayHasKey('id', $jsonArray, 'json has key id');
        Assert::assertArrayHasKey('norsk', $jsonArray, 'json has key norsk');
        Assert::assertArrayHasKey('german', $jsonArray, 'json has key german');
    }


    #[Then('heinz trained successfully :state norsk verb with id :number')]
    public function heinzTrainedSuccessfullyANorskVerbWithIdVerb(string $state, string $id): void
    {
        $request = new Request($this->patchMethod, $this->uri, $this->requestHeaders);
        $this->request = $request->withAttribute('id', $id);
    }


    #[Then('the result should be saved successfully for id :number with :counter')]
    public function theResultShouldBeSavedSuccessfully(string $id, string $expectedCounter): void
    {
        $expectedResponseBody = __DIR__ . '/resources/responses/randomVerb.json';
        $expectedResponse = new Response(
            204,
            $this->responseHeaders,
            file_get_contents($expectedResponseBody)
        );

        Assert::assertEquals(
            $expectedResponse->getStatusCode(),
            $this->response->getStatusCode(),
            'status codes match'
        );
        Assert::assertEquals(
            $expectedResponse->getHeaders(),
            $this->response->getHeaders(),
            'headers match'
        );

        $sql = GenericSqlStatement::create(
            'SELECT SuccessCounter FROM ' . $this->verbsSuccessCounterToUsersTable->value
            . ' WHERE username = ? AND verbId = ?;'
        );
        $params = Parameters::init();
        $params->addString('heinz');
        $params->addInt((int)$id);
        $result = $this->integrationDatabase->select($sql, $params);
        $successCounter = $result->asArray()[0]['SuccessCounter'];
        Assert::assertEquals((int)$expectedCounter, $successCounter);
    }


    #[When('there are no verbs in the database')]
    public function thereAreNoVerbsInTheDatabase(): void
    {
        $this->integrationDatabase->truncate($this->verbsSuccessCounterToUsersTable);
        $this->integrationDatabase->truncate($this->verbsTable);
    }


    #[Then(':someone should get an error :number :message while :service')]
    public function someoneShouldGetAnError(string $name, string $number, string $message, string $service): void
    {
        if ($service === 'saving') {
            $this->theResultIsSavedFor($name);
        }

        $expectedResponse = new Response(
            (int)$number,
            $this->responseHeaders,
            $message
        );

        $body = $this->response->getBody()->getContents();
        Assert::assertEquals(
            $expectedResponse->getStatusCode(),
            $this->response->getStatusCode(),
            'status codes match'
        );
        Assert::assertEquals($expectedResponse->getHeaders(), $this->response->getHeaders(), 'headers match');
        Assert::assertEquals($message, $body, 'message match');
    }


    #[When('the result is saved for :someone')]
    public function theResultIsSavedFor(string $name): void
    {
        $this->integrationDatabase->waitForDatabase();
        $this->response = $this->verbTrainer->saveSuccess(JwtUserProvider::getUser($name), $this->request);
    }


    #[Then('the result was not saved for id :number')]
    public function theResultIsNotSaved(string $id): void
    {
        $this->integrationDatabase->waitForDatabase();
        $sql = GenericSqlStatement::create(
            'SELECT SuccessCounter FROM ' . $this->verbsSuccessCounterToUsersTable->value
            . ' WHERE username = ? AND verbId = ?;'
        );
        $params = Parameters::init();
        $params->addString('heinz');
        $params->addInt((int)$id);
        $result = $this->integrationDatabase->select($sql, $params);
        $successCounter = $result->asArray()[0]['SuccessCounter'];

        Assert::assertEquals(10, $successCounter);
    }


    #[AfterScenario]
    public function after(): void
    {
        session_destroy();

        $this->integrationDatabase->truncate($this->verbsSuccessCounterToUsersTable);
        $this->integrationDatabase->truncate($this->usersTable);
        $this->integrationDatabase->truncate($this->verbsTable);

        $this->removeLog($this->logPath);
    }
}
