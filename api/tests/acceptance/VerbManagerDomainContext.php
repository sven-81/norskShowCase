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
use norsk\api\app\config\AppConfig;
use norsk\api\app\config\DbConfig;
use norsk\api\app\config\Path;
use norsk\api\app\logging\Logger;
use norsk\api\app\persistence\DbConnection;
use norsk\api\app\persistence\GenericSqlStatement;
use norsk\api\app\persistence\MysqliWrapper;
use norsk\api\app\persistence\Parameters;
use norsk\api\app\persistence\SqlResult;
use norsk\api\app\persistence\TableName;
use norsk\api\app\response\Url;
use norsk\api\helperTools\RouteMockHelper;
use norsk\api\helperTools\Removable;
use norsk\api\manager\ManagerWriter;
use norsk\api\manager\verbs\VerbManager;
use norsk\api\manager\verbs\VerbReader;
use norsk\api\shared\Json;
use norsk\api\tests\provider\TestHeader;
use norsk\api\tests\stubs\VirtualTestDatabase;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class VerbManagerDomainContext implements Context
{
    use Removable;
    use TestHeader;

    private VerbManager $verbManager;

    private TableName $usersTable;

    private VirtualTestDatabase $integrationDatabase;

    private array $requestHeaders;

    private array $responseHeaders;

    private string $uri;

    private ResponseInterface $response;

    private ServerRequestInterface $request;

    private TableName $verbsTable;

    private TableName $verbsSuccessCounterToUsersTable;

    private string $putMethod;

    private string $body;

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
        $verbReader = new VerbReader($database);
        $verbWriter = new ManagerWriter($database);
        $this->logPath = $appConfig->getLogPath();
        $logger = Logger::create($this->logPath);

        $this->url = Url::by('http://foo');
        $this->verbManager = new VerbManager($logger, $verbReader, $verbWriter, $this->url);
    }


    #[BeforeScenario]
    public function before(): void
    {
        $this->prepareTestDatabase();

        $this->requestHeaders = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . file_get_contents(__DIR__ . '/resources/jwt/heinz.manager.jwt'),
        ];

        $this->responseHeaders = $this->getTestHeaderAsHeaders($this->url);
        $this->putMethod = 'PUT';
        $this->uri = 'localhost:9999/api/manage/verbs';

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
    }


    #[BeforeScenario('successFactor')]
    public function prepareSuccessCounter(): void
    {
        $this->verbsSuccessCounterToUsersTable = TableName::verbsSuccessCounterToUsers;
        $this->integrationDatabase = VirtualTestDatabase::create($this->dbConfig);
        $initialVerbsToUserEntries = file_get_contents(__DIR__ . '/resources/verbsSql/verbToUser.sql');
        $this->integrationDatabase->insertInitialEntryToAvoidFailing($initialVerbsToUserEntries);
    }


    #[Given('there is a manager with the username :heinz')]
    public function thereIsAManagerWithTheUsername(string $heinz): void
    {
        $initialUser = GenericSqlStatement::create(
            'SELECT username as ' . $heinz . ' '
            . 'FROM ' . $this->usersTable->value . ' '
            . 'WHERE username = ? AND role = ?;'
        );

        $params = Parameters::init();
        $params->addString($heinz);
        $params->addString('manager');

        $result = $this->integrationDatabase->select($initialUser, $params);

        Assert::assertCount(1, $result, 'found ' . $heinz . ' as username in database');
    }


    #[Given(':heinz is an :active manager')]
    public function isAnManager(string $heinz, string $activity): void
    {
        if ($activity === 'active') {
            $active = 1;
        } else {
            $active = 0;

            $updateUser = GenericSqlStatement::create(
                "UPDATE `users` SET `active`=0 WHERE  `username`='" . $heinz . "';"
            );

            $params = Parameters::init();
            $this->integrationDatabase->update($updateUser, $params);
        }

        $initialUser = GenericSqlStatement::create(
            'SELECT username as ' . $heinz . ' '
            . 'FROM ' . $this->usersTable->value . ' '
            . 'WHERE username = ? '
            . 'AND active = ? AND role = ?;'
        );

        $params = Parameters::init();
        $params->addString($heinz);
        $params->addInt($active);
        $params->addString('manager');

        $result = $this->integrationDatabase->select($initialUser, $params);
        $_SESSION['user'] = $heinz;

        Assert::assertCount(1, $result, 'found ' . $heinz . ' as active manager in database');
    }


    #[When('I like to get a list of all verbs as :someone')]
    public function iLikeToGetAListOfAllVerbsAs(): void
    {
        $this->integrationDatabase->waitForDatabase();
        $this->response = $this->verbManager->getAllVerbs();
    }


    #[Then('I should get a list of all verbs')]
    public function iShouldGetAListOfAllVerbs(): void
    {
        $expectedResponseFile = __DIR__ . '/resources/responses/allVerbs.json';
        $expectedResponseBody = file_get_contents($expectedResponseFile);
        $expectedResponse = new Response(
            200,
            $this->responseHeaders,
            $expectedResponseBody
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

        Assert::assertJsonStringEqualsJsonString(
            $expectedResponseBody,
            $contents
        );
    }


    #[Given('the database is empty')]
    public function theDatabaseIsEmpty(): void
    {
        $result = SqlResult::resultFromArray([[1]]);
        while ($result->count() > 0) {
            $this->integrationDatabase->deleteAll($this->verbsTable);
            $this->integrationDatabase->waitForDatabase();

            $verbs = GenericSqlStatement::create(
                'SELECT id '
                . 'FROM ' . $this->verbsTable->value . ';'
            );

            $params = Parameters::init();
            $result = $this->integrationDatabase->select($verbs, $params);
        }
        Assert::assertCount(0, $result, 'no verbs in database');
    }


    #[Then('heinz should get an error :number :message')]
    public function heinzShouldGetAnError(string $number, string $message): void
    {
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


    #[When('I like to edit a :language verb with id :number')]
    public function iLikeToEditAVerbAs(string $language, string $id): void
    {
        $request = new Request($this->putMethod, $this->uri, $this->requestHeaders);
        $parserMock = RouteMockHelper::createRouteParserMock();
        $resultsMock = RouteMockHelper::createRoutingResultsMock();
        $routeMock = RouteMockHelper::createRouteMock();
        $routeMock->method('getArgument')
            ->willReturn($id);

        $request = $request->withAttribute('__routeParser__', $parserMock);
        $request = $request->withAttribute('__routingResults__', $resultsMock);
        $request = $request->withAttribute('__route__', $routeMock);

        $body = $this->getEditedLanguageBody($language);

        $bodyArray = Json::fromString($body)->asDecodedJson();
        $this->request = $request->withParsedBody($bodyArray);

        $this->response = $this->verbManager->update($this->request);
    }


    private function getEditedLanguageBody(string $language): string
    {
        if ($language === 'german') {
            return '{"german":"trinken","norsk":"spise","norskPresent":"spiser",'
                   . '"norskPast":"spiste","norskPastPerfect":"har spist"}';
        }
        if ($language === 'german only') {
            return '{"german":"laufen","norsk":"spise","norskPresent":"spiser",'
                   . '"norskPast":"spiste","norskPastPerfect":"har spist"}';
        }

        if ($language === 'norsk') {
            return '{"german":"essen","norsk":"drikke","norskPresent":"spiser",'
                   . '"norskPast":"spiste","norskPastPerfect":"har spist"}';
        }
        if ($language === 'norsk only') {
            return '{"german":"essen","norsk":"løpe","norskPresent":"spiser",'
                   . '"norskPast":"spiste","norskPastPerfect":"har spist"}';
        }
        if ($language === 'german and norsk') {
            return '{"german":"laufen","norsk":"løpe","norskPresent":"løper",'
                   . '"norskPast":"løp","norskPastPerfect":"har løpt"}';
        }

        return '{"german":"essen","norsk":"spise","norskPresent":"spiser",'
               . '"norskPast":"spiste","norskPastPerfect":"har spist"}';
    }


    #[When('I like to edit a verb with id 3 with an already existing :language verb')]
    public function iLikeToEditAVerbWithIdWithAnAlreadyExistingVerb(string $language): void
    {
        $request = new Request($this->putMethod, $this->uri, $this->requestHeaders);
        $parserMock = RouteMockHelper::createRouteParserMock();
        $resultsMock = RouteMockHelper::createRoutingResultsMock();
        $routeMock = RouteMockHelper::createRouteMock();
        $routeMock->method('getArgument')
            ->willReturn('3');

        $request = $request->withAttribute('__routeParser__', $parserMock);
        $request = $request->withAttribute('__routingResults__', $resultsMock);
        $request = $request->withAttribute('__route__', $routeMock);

        $body = $this->getCreatedLanguageBody($language);

        $bodyArray = Json::fromString($body)->asDecodedJson();
        $this->request = $request->withParsedBody($bodyArray);
        $this->integrationDatabase->waitForDatabase();

        $this->response = $this->verbManager->update($this->request);
    }


    private function getCreatedLanguageBody(string $language): string
    {
        if ($language === 'german') {
            return '{"german":"trinken","norsk":"spise","norskPresent":"spiser",'
                   . '"norskPast":"spiste","norskPastPerfect":"har spist"}';
        }

        if ($language === 'norsk') {
            return '{"german":"essen","norsk":"drikke","norskPresent":"spiser",'
                   . '"norskPast":"spiste","norskPastPerfect":"har spist"}';
        }

        return '{"german":"trinken","norsk":"drikke","norskPresent":"drikker",'
               . '"norskPast":"drakk","norskPastPerfect":"har drukket"}';
    }


    #[Then('the edited :language verb :input should be saved for id :number')]
    public function theEditedVerbShouldBeSaved(string $language, string $input, string $id): void
    {
        $sql = GenericSqlStatement::create(
            'SELECT german, norsk, norsk_present, norsk_past, norsk_past_perfekt'
            . ' FROM ' . $this->verbsTable->value
            . ' WHERE id = ?;'
        );
        $params = Parameters::init();
        $params->addInt((int)$id);
        $result = $this->integrationDatabase->select($sql, $params);
        $german = $result->asArray()[0]['german'];
        $norsk = $result->asArray()[0]['norsk'];
        $norskPresent = $result->asArray()[0]['norsk_present'];
        $norskPast = $result->asArray()[0]['norsk_past'];
        $norskPastPerfect = $result->asArray()[0]['norsk_past_perfekt'];

        $expected = match ($language) {
            'norsk' => [
                'norsk' => $input,
                'german' => 'laufen',
                'norskPresent' => 'spiser',
                'norskPast' => 'spiste',
                'norskPastPerfect' => 'har spist',
            ],
            'german' => [
                'norsk' => 'løpe',
                'german' => $input,
                'norskPresent' => 'løper',
                'norskPast' => 'løp',
                'norskPastPerfect' => 'har løpt',
            ],
            'german only' => [
                'norsk' => 'spise',
                'german' => 'laufen',
                'norskPresent' => 'spiser',
                'norskPast' => 'spiste',
                'norskPastPerfect' => 'har spist',
            ],
            'norsk only' => [
                'norsk' => 'løpe',
                'german' => 'essen',
                'norskPresent' => 'spiser',
                'norskPast' => 'spiste',
                'norskPastPerfect' => 'har spist',
            ],
            default => [
                'norsk' => 'løpe',
                'german' => 'laufen',
                'norskPresent' => 'løper',
                'norskPast' => 'løp',
                'norskPastPerfect' => 'har løpt',
            ],
        };

        Assert::assertEquals($expected['german'], $german);
        Assert::assertEquals($expected['norsk'], $norsk);
        Assert::assertEquals($expected['norskPresent'], $norskPresent);
        Assert::assertEquals($expected['norskPast'], $norskPast);
        Assert::assertEquals($expected['norskPastPerfect'], $norskPastPerfect);
    }


    #[Then('No content should be returned')]
    public function noContentShouldBeReturned(): void
    {
        $expectedResponse = new Response(
            204,
            $this->responseHeaders,
            ''
        );

        $body = $this->response->getBody()->getContents();
        Assert::assertEquals(
            $expectedResponse->getStatusCode(),
            $this->response->getStatusCode(),
            'status codes match'
        );
        Assert::assertEquals($expectedResponse->getHeaders(), $this->response->getHeaders(), 'headers match');
        Assert::assertEquals('', $body, 'message match');
    }


    #[When('I like to delete a verb with id :number')]
    public function iLikeToDeleteAVerbWithId(string $id): void
    {
        $request = new Request($this->putMethod, $this->uri, $this->requestHeaders);
        $parserMock = RouteMockHelper::createRouteParserMock();
        $resultsMock = RouteMockHelper::createRoutingResultsMock();
        $routeMock = RouteMockHelper::createRouteMock();
        $routeMock->method('getArgument')
            ->willReturn($id);

        $request = $request->withAttribute('__routeParser__', $parserMock);
        $request = $request->withAttribute('__routingResults__', $resultsMock);
        $this->request = $request->withAttribute('__route__', $routeMock);
        $this->integrationDatabase->waitForDatabase();

        $this->response = $this->verbManager->delete($this->request);
    }


    #[Then('the deleted verb :id should not be active anymore')]
    public function theDeletedVerbShouldNotBeActiveAnymore(string $id): void
    {
        $sql = GenericSqlStatement::create(
            'SELECT id FROM ' . $this->verbsTable->value
            . ' WHERE id = ? AND active=0;'
        );
        $params = Parameters::init();
        $params->addInt((int)$id);
        $result = $this->integrationDatabase->select($sql, $params);

        Assert::assertCount(1, $result->asArray());
    }


    #[Then('heinz should get a message :code :message')]
    public function heinzShouldGetAMessage(string $code, string $message): void
    {
        $expectedResponse = new Response(
            (int)$code,
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


    #[When('I like to add :state verb for :language')]
    public function iLikeToAddVerbFor(string $state, string $language): void
    {
        if ($state === 'a new' && $language === 'german') {
            $this->body = '{"german":"neu","norsk":"spise","norskPresent":"spiser",'
                          . '"norskPast":"spiste","norskPastPerfect":"har spist"}';
        } elseif ($state === 'a new' && $language === 'norsk') {
            $this->body = '{"german":"essen","norsk":"ny","norskPresent":"spiser",'
                          . '"norskPast":"spiste","norskPastPerfect":"har spist"}';
        } elseif ($state === 'an existing and active' && $language === 'german and norsk') {
            $this->body = '{"german":"trinken","norsk":"drikke","norskPresent":"drikker",'
                          . '"norskPast":"drakk","norskPastPerfect":"har drukket"}';
        } elseif ($state === 'an existing but inactive' && $language === 'german and norsk') {
            $this->body = '{"german":"sehen","norsk":"se","norskPresent":"ser",'
                          . '"norskPast":"så","norskPastPerfect":"har sett"}';
        } else {
            $this->body = '{"german":"laufen","norsk":"løpe","norskPresent":"løper",'
                          . '"norskPast":"løp","norskPastPerfect":"har løpt"}';
        }

        $request = new Request($this->putMethod, $this->uri, $this->requestHeaders);
        $parserMock = RouteMockHelper::createRouteParserMock();
        $resultsMock = RouteMockHelper::createRoutingResultsMock();

        $request = $request->withAttribute('__routeParser__', $parserMock);
        $request = $request->withAttribute('__routingResults__', $resultsMock);

        $bodyArray = Json::fromString($this->body)->asDecodedJson();
        $this->request = $request->withParsedBody($bodyArray);
        $this->integrationDatabase->waitForDatabase();

        $this->response = $this->verbManager->createVerb($this->request);
    }


    #[Then('the added verb should :state saved')]
    public function theAddedVerbShouldSaved(string $state): void
    {
        $bodyArray = Json::fromString($this->body)->asDecodedJson();
        $german = $bodyArray['german'];
        $norsk = $bodyArray['norsk'];

        $sql = GenericSqlStatement::create(
            'SELECT id FROM ' . $this->verbsTable->value
            . ' WHERE german = ? AND norsk= ? AND active=1;'
        );
        $params = Parameters::init();
        $params->addString($german);
        $params->addString($norsk);
        $result = $this->integrationDatabase->select($sql, $params);

        if ($state === 'not be') {
            Assert::assertCount(0, $result->asArray());
        } else {
            Assert::assertCount(1, $result->asArray());
        }
    }


    #[AfterScenario('successFactor')]
    public function truncateSuccessFactorTable(): void
    {
        $this->integrationDatabase->truncate($this->verbsSuccessCounterToUsersTable);
    }


    #[AfterScenario]
    public function after(): void
    {
        session_destroy();
        $this->integrationDatabase->truncate($this->usersTable);
        $this->integrationDatabase->truncate($this->verbsTable);

        $this->removeLog($this->logPath);
    }
}
