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
use InvalidArgumentException;
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
use norsk\api\tests\provider\TestHeader;
use norsk\api\tests\stubs\MutableTestClock;
use norsk\api\tests\stubs\VirtualTestDatabase;
use norsk\api\user\infrastructure\identityAccessManagement\EnhancedClock;
use norsk\api\user\infrastructure\identityAccessManagement\jwt\JwtManagement;
use norsk\api\user\infrastructure\persistence\UsersReader;
use norsk\api\user\infrastructure\UserManagementFactory;
use norsk\api\user\infrastructure\web\controller\Login;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;

error_reporting(error_reporting() & ~E_WARNING);

class UserLoginDomainContext implements Context
{
    use Removable;
    use TestHeader;

    private Login $login;

    private TableName $tableName;

    private VirtualTestDatabase $integrationDatabase;

    private array $requestHeaders;

    private array $responseHeaders;

    private string $method;

    private string $uri;

    private string $password;

    private ResponseInterface $response;

    private Request $request;

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
        $usersReader = new UsersReader($database);
        $this->logPath = $appConfig->getLogPath();
        $logger = Logger::create($this->logPath);

        $testClock = new MutableTestClock();
        $enhancedClock = new EnhancedClock($testClock);
        $jwtManagement = new JwtManagement($appConfig, $enhancedClock, $logger);

        $this->url = Url::by('http://foo');

        $context = new UserManagementFactory($logger, $database, $jwtManagement, $appConfig);
        $this->login = $context->login();
    }


    #[BeforeScenario]
    public function before(): void
    {
        $this->prepareTestDatabase();

        $this->requestHeaders = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . file_get_contents(__DIR__ . '/resources/jwt/expectedTest.jwt'),
        ];

        $this->responseHeaders = $this->getTestHeaderAsHeaders($this->url);
        $this->method = 'POST';
        $this->uri = 'localhost:9999/api/user';
    }


    private function prepareTestDatabase(): void
    {
        $this->tableName = TableName::users;
        $this->integrationDatabase = VirtualTestDatabase::create($this->dbConfig);
        $initialEntry = file_get_contents(__DIR__ . '/resources/usersSql/initial.sql');
        $this->integrationDatabase->insertInitialEntryToAvoidFailing($initialEntry);
    }


    #[Given('there is a user with the username :heinz')]
    public function thereIsAUserWithTheUsername($heinz): void
    {
        $initialUser = GenericSqlStatement::create(
            'SELECT username as ' . $heinz . ' '
            . 'FROM ' . $this->tableName->value . ' '
            . 'WHERE username = ?;'
        );

        $params = Parameters::init();
        $params->addString($heinz);

        $result = $this->integrationDatabase->select($initialUser, $params);

        Assert::assertCount(1, $result, 'found ' . $heinz . ' as username in database');
    }


    #[Given('there is no user with the username klaus')]
    public function thereIsNoUserWithTheUsernameKlaus(): void
    {
        $initialUser = GenericSqlStatement::create(
            'SELECT username as klaus '
            . 'FROM ' . $this->tableName->value . ' '
            . 'WHERE username = ?;'
        );

        $params = Parameters::init();
        $params->addString('klaus');

        $result = $this->integrationDatabase->select($initialUser, $params);

        Assert::assertCount(0, $result, 'did not find klaus as username in database');
    }


    #[Given(':heinz is an :active user')]
    public function getActivityForUser($heinz, $activity): void
    {
        if ($activity === 'active') {
            $active = 1;
        } else {
            $active = 0;
        }

        $initialUser = GenericSqlStatement::create(
            'SELECT username as ' . $heinz . ' '
            . 'FROM ' . $this->tableName->value . ' '
            . 'WHERE username = ? '
            . 'AND active = ?;'
        );

        $params = Parameters::init();
        $params->addString($heinz);
        $params->addInt($active);

        $result = $this->integrationDatabase->select($initialUser, $params);

        Assert::assertCount(1, $result, 'found ' . $heinz . ' as active username in database');
    }


    #[When('I provide the :correct password for :heinz')]
    public function iProvideAPasswordThatIs(string $correctly, string $heinz): bool
    {
        if ($correctly === 'correct') {
            $this->password = "someSecretlySecret";

            return true;
        }

        $this->password = "somethingWrong";

        return false;
    }


    #[When('I login with the username :someone')]
    public function iLoginWithTheUsername($someone): void
    {
        $body = '{"username": "' . $someone . '","password": "' . $this->password . '"}';
        $bodyArray = Json::fromString($body)->asDecodedJson();

        $request = new Request($this->method, $this->uri, $this->requestHeaders);
        $requestWithParsedBody = $request->withParsedBody($bodyArray);

        $this->response = $this->login->run($requestWithParsedBody);
    }


    #[When('I login')]
    public function iLogin(): void
    {
        $this->response = $this->login->run($this->request);
    }


    #[Then('I should get an error :number :message')]
    public function iShouldGetAnError(string $number, string $message): void
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


    #[Then('I should have been logged in as :heinz')]
    public function iShouldHaveBeenLoggedIn(): void
    {
        $expectedResponseBody = __DIR__ . '/resources/responses/loggedIn.json';
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

        Assert::assertArrayIsEqualToArrayIgnoringListOfKeys(
            [
                'login' => true,
                'username' => 'heinz',
                'firstName' => 'Heinz',
                'lastName' => 'Klaus',
                'tokenType' => 'Bearer',
                'expiresIn' => 7200,
            ],
            $jsonArray,
            ['token'],
            'response bodies match'
        );

        Assert::assertStringStartsWith(
            'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.'
            . 'eyJzdWIiOiJub3JzayBhcHAiLCJhdWQiOiJOb3Jzay',
            $jsonArray['token'],
            'token has correct beginning'
        );
    }


    #[Then('I should not have been logged in as karl since forbidden')]
    public function iShouldNotHaveBeenLoggedInAsKarlSinceForbidden(): void
    {
        $expectedResponseBody = '{"message":"Forbidden: user is not active"}';
        $expectedResponse = new Response(
            403,
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
        $jsonArray = Json::fromString($contents)->asDecodedJson();
        $json = Json::encodeFromArray($jsonArray);
        $jsonString = $json->asString();

        Assert::assertJsonStringEqualsJsonString(
            $expectedResponseBody,
            $jsonString
        );
    }


    #[Then('I should not have been logged in as :someone')]
    public function iShouldNotHaveBeenLoggedIn(): void
    {
        $expectedResponseBody = '{"message":"Unauthorized: Cannot verify credentials"}';
        $expectedResponse = new Response(
            401,
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
        $jsonArray = Json::fromString($contents)->asDecodedJson();
        $json = Json::encodeFromArray($jsonArray);
        $jsonString = $json->asString();

        Assert::assertJsonStringEqualsJsonString(
            $expectedResponseBody,
            $jsonString
        );
    }


    #[Given('request is missing :parameter')]
    public function requestIsMissing(string $parameter): void
    {
        $body = match ($parameter) {
            'username' => '{"password": "myVerySecretlySecret"}',
            'password' => '{"username": "heinz"}',
            default => throw new InvalidArgumentException('parameter musst be username or password')
        };

        $bodyArray = Json::fromString($body)->asDecodedJson();

        $request = new Request($this->method, $this->uri, $this->requestHeaders);
        $this->request = $request->withParsedBody($bodyArray);
    }


    #[AfterScenario]
    public function after(): void
    {
        $this->integrationDatabase->truncate($this->tableName);
        $this->removeLog($this->logPath);
    }
}
