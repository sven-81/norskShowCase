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
use norsk\api\app\config\AppConfig;
use norsk\api\app\config\DbConfig;
use norsk\api\app\config\Path;
use norsk\api\app\logging\Logger;
use norsk\api\app\persistence\DbConnection;
use norsk\api\app\persistence\GenericSqlStatement;
use norsk\api\app\persistence\MysqliWrapper;
use norsk\api\app\persistence\Parameters;
use norsk\api\app\persistence\TableName;
use norsk\api\app\response\Url;
use norsk\api\helperTools\Removable;
use norsk\api\shared\Json;
use norsk\api\tests\provider\TestHeader;
use norsk\api\tests\stubs\VirtualTestDatabase;
use norsk\api\user\PasswordVector;
use norsk\api\user\Registration;
use norsk\api\user\Salt;
use norsk\api\user\UsersWriter;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;

class UserRegistrationDomainContext implements Context
{
    use Removable;
    use TestHeader;

    private Registration $registration;

    private ResponseInterface $response;

    private VirtualTestDatabase $integrationDatabase;

    private TableName $tableName;

    private Request $request;

    private array $requestHeaders;

    private array $responseHeaders;

    private string $uri;

    private string $method;

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
        $usersWriter = new UsersWriter($database);
        $this->logPath = $appConfig->getLogPath();
        $logger = Logger::create($this->logPath);

        $salt = Salt::init();
        $salt->generate();
        $pepper = $appConfig->getPepper();
        $passwordVector = PasswordVector::by($salt, $pepper);

        $this->url = Url::by('http://foo');
        $this->registration = new Registration($logger, $usersWriter, $passwordVector, $this->url);
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
        $this->uri = 'localhost:9999/api/user/new';
    }


    private function prepareTestDatabase(): void
    {
        $this->tableName = TableName::users;
        $this->integrationDatabase = VirtualTestDatabase::create($this->dbConfig);
        $initialEntry = file_get_contents(__DIR__ . '/resources/usersSql/initial.sql');
        $this->integrationDatabase->insertInitialEntryToAvoidFailing($initialEntry);
    }


    #[Given('there is no user yet with the username :klaus')]
    public function thereIsNoUserYetWithTheUsername(string $klaus): void
    {
        $initialUser = GenericSqlStatement::create(
            'SELECT username as ' . $klaus . ' '
            . 'FROM ' . $this->tableName->value . ' '
            . 'WHERE username = ?'
        );

        $params = Parameters::init();
        $params->addString($klaus);

        $result = $this->integrationDatabase->select($initialUser, $params);

        Assert::assertCount(0, $result, 'no username klaus in database');
    }


    #[When('I register with the username Klaus')]
    public function iRegisterWithTheUsernameKlaus(): void
    {
        $body = '{"firstName": "Karl-Klaus","lastName": "Tausch",'
                . '"username": "Klaus","password": "myVerySecretlySecret"}';

        $request = new Request($this->method, $this->uri, $this->requestHeaders);
        $bodyArray = Json::fromString($body)->asDecodedJson();
        $requestWithParsedBody = $request->withParsedBody($bodyArray);

        $this->response = $this->registration->registerUser($requestWithParsedBody);
    }


    #[Then('I should have been registered as :klaus')]
    public function iShouldHaveBeenRegisteredAs(string $klaus): void
    {
        $expectedResponse = new Response(
            201,
            $this->responseHeaders,
            '{}'
        );

        $parameters = Parameters::init();
        $parameters->addString($klaus);
        $actualEntry = $this->integrationDatabase->select(
            GenericSqlStatement::create(
                "SELECT username, firstname, lastname, password_hash, active "
                . "FROM " . $this->tableName->value . " WHERE username = ?"
            ),
            $parameters
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

        Assert::assertArrayIsEqualToArrayIgnoringListOfKeys(
            [
                'username' => 'Klaus',
                'firstname' => 'Karl-Klaus',
                'lastname' => 'Tausch',
                'password_hash' => 'myVerySecretlySecret',
                'active' => false,
            ],
            $actualEntry->asArray()[0],
            ['password_hash'],
            'username Klaus was written into database'
        );
    }


    #[Given('there is already a user with the username :heinz')]
    public function thereIsAlreadyAUserWithTheUsername(string $heinz): void
    {
        $initialUser = GenericSqlStatement::create(
            'SELECT username as ' . $heinz . ' '
            . 'FROM ' . $this->tableName->value . ' '
            . 'WHERE username = ?'
        );

        $params = Parameters::init();
        $params->addString($heinz);

        $result = $this->integrationDatabase->select($initialUser, $params);

        Assert::assertCount(1, $result, 'username heinz already in database');
    }


    #[When('I register with the taken username heinz')]
    public function iRegisterWithTheTakenUsernameHeinz(): void
    {
        $body = '{"firstName": "Karl-Heinz","lastName": "Tausch",'
                . '"username": "heinz","password": "myVerySecretlySecret"}';

        $request = new Request($this->method, $this->uri, $this->requestHeaders);
        $bodyArray = Json::fromString($body)->asDecodedJson();
        $requestWithParsedBody = $request->withParsedBody($bodyArray);

        $this->response = $this->registration->registerUser($requestWithParsedBody);
    }


    #[Given('request is missing :parameter')]
    public function requestIsMissing($parameter): void
    {
        $body = match ($parameter) {
            'username' => '{"firstName": "Karl-Heinz","lastName": "Tausch","password": "myVerySecretlySecret"}',
            'firstname' => '{"lastName": "Tausch","username": "heinz","password": "myVerySecretlySecret"}',
            'lastname' => '{"firstName": "Karl-Heinz","username": "heinz","password": "myVerySecretlySecret"}',
            'password' => '{"firstName": "Karl-Heinz","lastName": "Tausch","username": "heinz"}',
            default => throw new InvalidArgumentException(
                'parameter musst be username, firstname, lastname or password'
            )
        };

        $request = new Request($this->method, $this->uri, $this->requestHeaders);
        $bodyArray = Json::fromString($body)->asDecodedJson();
        $this->request = $request->withParsedBody($bodyArray);
    }


    #[Given('request has a short password')]
    public function requestHasAShortPassword(): void
    {
        $body = '{"firstName": "Karl-Heinz","lastName": "Tausch",'
                . '"username": "heinz","password": "tooShort"}';

        $request = new Request($this->method, $this->uri, $this->requestHeaders);
        $bodyArray = Json::fromString($body)->asDecodedJson();
        $this->request = $request->withParsedBody($bodyArray);
    }


    #[When('I register')]
    public function iRegister(): void
    {
        $this->response = $this->registration->registerUser($this->request);
    }


    #[Then('I should get an error :number :message')]
    public function iShouldGetAnError(string $number, string $message): void
    {
        $expectedResponse = new Response(
            (int)$number,
            $this->responseHeaders,
            $message
        );

        Assert::assertEquals(
            $expectedResponse->getStatusCode(),
            $this->response->getStatusCode(),
            'status codes match'
        );
        Assert::assertEquals($expectedResponse->getHeaders(), $this->response->getHeaders(), 'headers match');
        $actualMessage = $this->response->getBody()->getContents();
        Assert::assertEquals($message, $actualMessage, 'message match');
    }


    #[AfterScenario]
    public function after(): void
    {
        $this->integrationDatabase->truncate($this->tableName);
        $this->removeLog($this->logPath);
    }
}
