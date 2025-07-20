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
use norsk\api\infrastructure\persistence\SqlResult;
use norsk\api\infrastructure\persistence\TableName;
use norsk\api\manager\infrastructure\ManagerFactory;
use norsk\api\manager\infrastructure\web\controller\WordManager;
use norsk\api\shared\application\Json;
use norsk\api\shared\infrastructure\http\response\Url;
use norsk\api\tests\provider\JwtUserProvider;
use norsk\api\tests\provider\TestHeader;
use norsk\api\tests\stubs\VirtualTestDatabase;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class WordManagerDomainContext implements Context
{
    use Removable;
    use TestHeader;

    private WordManager $wordManager;

    private TableName $usersTable;

    private VirtualTestDatabase $integrationDatabase;

    private array $requestHeaders;

    private array $responseHeaders;

    private string $uri;

    private ResponseInterface $response;

    private ServerRequestInterface $request;

    private TableName $wordsTable;

    private TableName $wordsSuccessCounterToUsersTable;

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
        $this->logPath = $appConfig->getLogPath();
        $logger = Logger::create($this->logPath);

        $this->url = Url::by('http://foo');

        $context = new ManagerFactory($logger, $database, $appConfig);
        $this->wordManager = $context->wordManager();
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
        $this->uri = 'localhost:9999/api/manage/words';

        session_start();
    }


    private function prepareTestDatabase(): void
    {
        $this->usersTable = TableName::users;
        $this->integrationDatabase = VirtualTestDatabase::create($this->dbConfig);
        $initialUserEntries = file_get_contents(__DIR__ . '/resources/usersSql/initial.sql');
        $this->integrationDatabase->insertInitialEntryToAvoidFailing($initialUserEntries);

        $this->wordsTable = TableName::words;
        $initialWordsEntries = file_get_contents(__DIR__ . '/resources/wordsSql/initial.sql');
        $this->integrationDatabase->insertInitialEntryToAvoidFailing($initialWordsEntries);
    }


    #[BeforeScenario('successFactor')]
    public function prepareSuccessCounter(): void
    {
        $this->wordsSuccessCounterToUsersTable = TableName::wordsSuccessCounterToUsers;
        $this->integrationDatabase = VirtualTestDatabase::create($this->dbConfig);
        $initialWordsToUserEntries = file_get_contents(__DIR__ . '/resources/wordsSql/wordToUser.sql');
        $this->integrationDatabase->insertInitialEntryToAvoidFailing($initialWordsToUserEntries);
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


    #[When(':someone likes to get a list of all words')]
    public function someoneLikesToGetAListOfAllWords(string $name): void
    {
        $this->integrationDatabase->waitForDatabase();
        $this->response = $this->wordManager->getAllWords(JwtUserProvider::getUser($name));
    }


    #[Then(':someone should get a list of all words')]
    public function someoneShouldGetAListOfAllWords(): void
    {
        $expectedResponseFile = __DIR__ . '/resources/responses/allWords.json';
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
            $this->integrationDatabase->deleteAll($this->wordsTable);
            $this->integrationDatabase->waitForDatabase();

            $words = GenericSqlStatement::create(
                'SELECT id '
                . 'FROM ' . $this->wordsTable->value . ';'
            );

            $params = Parameters::init();
            $result = $this->integrationDatabase->select($words, $params);
        }
        Assert::assertCount(0, $result, 'no words in database');
    }


    #[Then(':someone should get an error :number :message')]
    public function someoneShouldGetAnError(string $name, string $number, string $message): void
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


    #[When(':someone likes to edit a :language word with id :number')]
    public function someoneLikesToEditAWordAs(string $name, string $language, string $id): void
    {
        $body = match ($language) {
            'german only' => '{"german":"neu","norsk":"skjærgård"}',
            'norsk only' => '{"german":"Schärenküste","norsk":"ny"}',
            default => '{"german":"neu","norsk":"ny"}',
        };

        $bodyArray = Json::fromString($body)->asDecodedJson();

        $request = new Request($this->putMethod, $this->uri, $this->requestHeaders);
        $this->request = $request
            ->withAttribute('id', $id)
            ->withParsedBody($bodyArray);

        $this->response = $this->wordManager->update(JwtUserProvider::getUser($name), $this->request);
    }


    #[When(':someone likes to edit a word with id 3 with an already existing :language word :case')]
    public function someoneLikesToEditAWordWithIdWithAnAlreadyExistingWord(
        string $name,
        string $language,
        string $case
    ): void {
        $body = $this->getLanguageBody($language, $case);
        $bodyArray = Json::fromString($body)->asDecodedJson();

        $request = new Request($this->putMethod, $this->uri, $this->requestHeaders);
        $this->request = $request
            ->withAttribute('id', '3')
            ->withParsedBody($bodyArray);
        $this->integrationDatabase->waitForDatabase();

        $this->response = $this->wordManager->update(JwtUserProvider::getUser($name), $this->request);
    }


    private function getLanguageBody(string $language, string $case): string
    {
        if ($language === 'german') {
            return match ($case) {
                'in same case' => '{"german":"Liebe","norsk":"ny"}',
                'case contrary' => '{"german":"liebe","norsk":"ny"}',
                default => '{"german":"Liebe","norsk":"ny"}',
            };
        }

        if ($language === 'norsk') {
            return '{"german":"neu","norsk":"kjærlighet"}';
        }

        return '{"german":"Liebe","norsk":"kjærlighet"}';
    }


    #[Then('the edited :language word :input should be saved for id :number')]
    public function theEditedWordShouldBeSaved(string $language, string $input, string $id): void
    {
        $this->integrationDatabase->waitForDatabase();
        $sql = GenericSqlStatement::create(
            'SELECT german, norsk FROM ' . $this->wordsTable->value
            . ' WHERE id = ?;'
        );
        $params = Parameters::init();
        $params->addInt((int)$id);
        $result = $this->integrationDatabase->select($sql, $params);
        $german = $result->asArray()[0]['german'];
        $norsk = $result->asArray()[0]['norsk'];

        $expected = match ($language) {
            'norsk' => ['expectedNorsk' => $input, 'expectedGerman' => 'neu'],
            'german' => ['expectedNorsk' => 'ny', 'expectedGerman' => $input],
            'german only' => ['expectedNorsk' => 'skjærgård', 'expectedGerman' => 'neu'],
            'norsk only' => ['expectedNorsk' => 'ny', 'expectedGerman' => 'Schärenküste'],
            default => ['expectedNorsk' => 'ny', 'expectedGerman' => 'neu'],
        };

        Assert::assertEquals($expected['expectedGerman'], $german);
        Assert::assertEquals($expected['expectedNorsk'], $norsk);
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


    #[When(':someone likes to delete a word with id :number')]
    public function someoneLikeToDeleteAWordWithId(string $name, string $id): void
    {
        $request = new Request($this->putMethod, $this->uri, $this->requestHeaders);
        $this->request = $request->withAttribute('id', $id);

        $this->integrationDatabase->waitForDatabase();

        $this->response = $this->wordManager->delete(JwtUserProvider::getUser($name), $this->request);
    }


    #[Then('the deleted word :id should not be active anymore')]
    public function theDeletedWordShouldNotBeActiveAnymore(string $id): void
    {
        $sql = GenericSqlStatement::create(
            'SELECT id FROM ' . $this->wordsTable->value
            . ' WHERE id = ? AND active=0;'
        );
        $params = Parameters::init();
        $params->addInt((int)$id);
        $result = $this->integrationDatabase->select($sql, $params);

        Assert::assertCount(1, $result->asArray());
    }


    #[Then(':someone should get a message :code :message')]
    public function someoneShouldGetAMessage(string $code, string $message): void
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


    #[When(':someone likes to add :state word for :language')]
    public function someoneLikeToAddWordFor(string $name, string $state, string $language): void
    {
        if ($state === 'a new' && $language === 'german') {
            $this->body = '{"german":"neu","norsk":"bølger"}';
        } elseif ($state === 'a new' && $language === 'norsk') {
            $this->body = '{"german":"Wellen","norsk":"ny"}';
        } elseif ($state === 'an existing in lowercase and active' && $language === 'german') {
            $this->body = '{"german":"wellen","norsk":"ny"}';
        } elseif ($state === 'an existing and active' && $language === 'german and norsk') {
            $this->body = '{"german":"Wellen","norsk":"bølger"}';
        } elseif ($state === 'an existing but inactive' && $language === 'german and norsk') {
            $this->body = '{"german":"Grün","norsk":"grønn"}';
        } else {
            $this->body = '{"german":"neu","norsk":"ny"}';
        }

        $bodyArray = Json::fromString($this->body)->asDecodedJson();

        $request = new Request($this->putMethod, $this->uri, $this->requestHeaders);
        $this->request = $request->withParsedBody($bodyArray);
        $this->integrationDatabase->waitForDatabase();

        $this->response = $this->wordManager->createWord(JwtUserProvider::getUser($name), $this->request);
    }


    #[Then('the added word should :state saved')]
    public function theAddedWordShouldSaved(string $state): void
    {
        $bodyArray = Json::fromString($this->body)->asDecodedJson();
        $german = $bodyArray['german'];
        $norsk = $bodyArray['norsk'];

        $sql = GenericSqlStatement::create(
            'SELECT id FROM ' . $this->wordsTable->value
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
        $this->integrationDatabase->truncate($this->wordsSuccessCounterToUsersTable);
    }


    #[AfterScenario]
    public function after(): void
    {
        session_destroy();
        $this->integrationDatabase->truncate($this->usersTable);
        $this->integrationDatabase->truncate($this->wordsTable);

        $this->removeLog($this->logPath);
    }
}
