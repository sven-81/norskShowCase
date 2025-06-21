<?php

declare(strict_types=1);

namespace norsk\api;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use norsk\api\app\config\DbConfig;
use norsk\api\app\config\Path;
use norsk\api\app\persistence\GenericSqlStatement;
use norsk\api\app\persistence\TableName;
use norsk\api\helperTools\Removable;
use norsk\api\shared\Json;
use norsk\api\tests\stubs\TestClient;
use norsk\api\tests\stubs\VirtualTestDatabase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
class WordManagerSystemTest extends TestCase
{
    use Removable;

    private const string GET_POST_WORDS = 'manage/words';

    private const string CHANGE = 'manage/words/';

    private VirtualTestDatabase $integrationDatabase;

    private string $uriGetWords;

    private string $uriCreateWord;

    private string $uriEditWord;

    private string $methodGet;

    private string $methodPost;

    private string $methodPut;

    private string $methodDelete;

    private string $bearerPathClient;

    private string $bearerPathManager;

    private Json $body;


    protected function setUp(): void
    {
        $dbConfig = DbConfig::fromPath(
            Path::fromString(__DIR__ . '/resources/config/mySqlConfig.ini')
        );
        $this->integrationDatabase = VirtualTestDatabase::create($dbConfig);

        $this->uriGetWords = self::GET_POST_WORDS;
        $this->uriCreateWord = self::GET_POST_WORDS;
        $this->uriEditWord = self::CHANGE;
        $this->methodGet = 'GET';
        $this->methodPost = 'POST';
        $this->methodPut = 'PUT';
        $this->methodDelete = 'DELETE';

        $this->bearerPathClient = __DIR__ . '/resources/jwts/userClient.jwt';
        $this->bearerPathManager = __DIR__ . '/resources/jwts/ottoActiveManager.jwt';

        $this->body = Json::fromString('{"german":"fo","norsk":"fa"}');
    }


    public static function getInvalidMethods(): array
    {
        return [
            'PUT@viewAndCreate' => ['PUT', self::GET_POST_WORDS],
            'PATCH@viewAndCreate' => ['PATCH', self::GET_POST_WORDS],
            'DELETE@viewAndCreate' => ['DELETE', self::GET_POST_WORDS],
            'GET@change' => ['GET', self::CHANGE . '1'],
            'PATCH@change' => ['PATCH', self::CHANGE . '1'],
            'POST@change' => ['POST', self::CHANGE . '1'],
        ];
    }


    public static function getMethodsForChanging(): array
    {
        return [
            'delete' => ['DELETE', self::CHANGE, null],
            'edit' => ['PUT', self::CHANGE, Json::fromString('{"german":"fo","norsk":"fa"}')],
        ];
    }


    public static function getInvalidManager(): array
    {
        $tokenDir = __DIR__ . '/resources/jwts/';
        $body = Json::fromString('{"german":"fo","norsk":"fa"}');

        return [
            'activeUser@viewing' => [
                $tokenDir . 'heinzActiveUser.jwt',
                'GET',
                self::GET_POST_WORDS,
                null,
                '{"message":"Unauthorized: No rights for managing words or verbs"}',
            ],
            'inactiveUser@viewing' => [
                $tokenDir . 'lenaInactiveUser.jwt',
                'GET',
                self::GET_POST_WORDS,
                null,
                '{"message":"Unauthorized: Cannot verify credentials"}',
            ],
            'inactiveManager@viewing' => [
                $tokenDir . 'peterInactiveManager.jwt',
                'GET',
                self::GET_POST_WORDS,
                null,
                '{"message":"Unauthorized: Cannot verify credentials"}',
            ],
            'activeUser@creating' => [
                $tokenDir . 'heinzActiveUser.jwt',
                'POST',
                self::GET_POST_WORDS,
                $body,
                '{"message":"Unauthorized: No rights for managing words or verbs"}',
            ],
            'inactiveUser@creating' => [
                $tokenDir . 'lenaInactiveUser.jwt',
                'POST',
                self::GET_POST_WORDS,
                $body,
                '{"message":"Unauthorized: Cannot verify credentials"}',
            ],
            'inactiveManager@creating' => [
                $tokenDir . 'peterInactiveManager.jwt',
                'POST',
                self::GET_POST_WORDS,
                $body,
                '{"message":"Unauthorized: Cannot verify credentials"}',
            ],
            'activeUser@editing' => [
                $tokenDir . 'heinzActiveUser.jwt',
                'PUT',
                self::CHANGE . '1',
                $body,
                '{"message":"Unauthorized: No rights for managing words or verbs"}',
            ],
            'inactiveUser@editing' => [
                $tokenDir . 'lenaInactiveUser.jwt',
                'PUT',
                self::CHANGE . '1',
                $body,
                '{"message":"Unauthorized: Cannot verify credentials"}',
            ],
            'inactiveManager@editing' => [
                $tokenDir . 'peterInactiveManager.jwt',
                'PUT',
                self::CHANGE . '1',
                $body,
                '{"message":"Unauthorized: Cannot verify credentials"}',
            ],
            'activeUser@deleting' => [
                $tokenDir . 'heinzActiveUser.jwt',
                'DELETE',
                self::CHANGE . '1',
                null,
                '{"message":"Unauthorized: No rights for managing words or verbs"}',
            ],
            'inactiveUser@deleting' => [
                $tokenDir . 'lenaInactiveUser.jwt',
                'DELETE',
                self::CHANGE . '1',
                null,
                '{"message":"Unauthorized: Cannot verify credentials"}',
            ],
            'inactiveManager@deleting' => [
                $tokenDir . 'peterInactiveManager.jwt',
                'DELETE',
                self::CHANGE . '1',
                null,
                '{"message":"Unauthorized: Cannot verify credentials"}',
            ],
        ];
    }


    #[DataProvider('getInvalidMethods')]
    public function testCanThrowMethodNotAllowException(string $method, string $uri): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionCode(405);
        $this->expectExceptionMessage('405 Method Not Allowed');

        $this->prepareDatabase();

        TestClient::createWithoutApiDocValidation($this->bearerPathClient, $method, $uri);
    }


    private function prepareDatabase(): void
    {
        $sqlUsers = file_get_contents(__DIR__ . '/resources/queries/initialUsers.sql');
        $this->integrationDatabase->insertInitialEntryToAvoidFailing($sqlUsers);

        $sqlWords = file_get_contents(__DIR__ . '/resources/queries/initialWords.sql');
        $this->integrationDatabase->insertInitialEntryToAvoidFailing($sqlWords);
    }


    public function testCanThrowServerErrorExceptionIfDatabaseIsEmptyWhileGettingWords(): void
    {
        $this->expectException(ServerException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('500 Internal Server Error');

        $sqlUsers = file_get_contents(__DIR__ . '/resources/queries/initialUsers.sql');
        $this->integrationDatabase->insertInitialEntryToAvoidFailing($sqlUsers);

        TestClient::createWithApiDocValidation($this->bearerPathManager, $this->methodGet, $this->uriGetWords);
    }


    public function testCanThrowServerErrorExceptionWhileEditingWord(): void
    {
        $this->expectException(ServerException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('500 Internal Server Error');

        $this->prepareDatabase();

        TestClient::createWithApiDocValidation(
            $this->bearerPathManager,
            $this->methodPut,
            $this->uriEditWord . '1',
            Json::encodeFromArray([])
        );
    }


    #[DataProvider('getMethodsForChanging')]
    public function testCanThrow404IfWordIsNotFoundForChanging(
        string $method,
        string $uri,
        ?Json $body
    ): void {
        $this->expectException(ClientException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage(
            '`404 Not Found` response:' . PHP_EOL .
            '{"message":"No record found in database for id: 11"}'
        );

        $this->prepareDatabase();

        TestClient::createWithApiDocValidation($this->bearerPathManager, $method, $uri . '11', $body);
    }


    public function testCanThrowExceptionIfTokenIsExpired(): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage(
            '`401 Unauthorized` response:' . PHP_EOL .
            '{"message":"Token expired: Expired token"}'
        );

        $this->prepareDatabase();
        $bearer = __DIR__ . '/resources/jwts/heinzActiveUserExpired.jwt';

        TestClient::createWithApiDocValidation($bearer, $this->methodGet, $this->uriGetWords);
    }


    public function testCanThrowServerErrorExceptionWhileSavingSuccess(): void
    {
        $this->expectException(ServerException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('500 Internal Server Error');

        $this->prepareDatabase();
        $sql = GenericSqlStatement::create(
            'ALTER TABLE `wordsSuccessCounterToUsers` DROP COLUMN `successCounter`;'
        );
        $this->integrationDatabase->alter($sql);

        TestClient::createWithApiDocValidation(
            $this->bearerPathManager,
            $this->methodPut,
            $this->uriEditWord . '1',
            Json::encodeFromArray([])
        );
    }


    public function testCanRouteManagerToGetAllWordsToTrain(): void
    {
        $this->prepareDatabase();
        $response = TestClient::createWithApiDocValidation(
            $this->bearerPathManager,
            $this->methodGet,
            $this->uriGetWords
        );
        $responseJson = Json::fromString((string)$response->getBody());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $expectedJson = __DIR__ . '/resources/json/wordsToTrain.json';
        $this->assertJsonStringEqualsJsonFile($expectedJson, $responseJson->asString());
    }


    public function testCanRouteManagerToEditWord(): void
    {
        $this->prepareDatabase();

        $response = TestClient::createWithApiDocValidation(
            $this->bearerPathManager,
            $this->methodPut,
            $this->uriEditWord . '1',
            $this->body
        );
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals('No Content', $response->getReasonPhrase());
    }


    public function testCanRouteManagerToDeleteWord(): void
    {
        $this->prepareDatabase();
        $response = TestClient::createWithApiDocValidation(
            $this->bearerPathManager,
            $this->methodDelete,
            $this->uriEditWord . '1'
        );
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
    }


    public function testCanRouteManagerToCreateWord(): void
    {
        $this->prepareDatabase();

        $response = TestClient::createWithApiDocValidation(
            $this->bearerPathManager,
            $this->methodPost,
            $this->uriCreateWord,
            $this->body
        );
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('Created', $response->getReasonPhrase());
    }


    #[DataProvider('getInvalidManager')]
    public function testCanThrowExceptionIfUserIsInvalidToGetWordToTrain(
        string $bearer,
        string $method,
        string $uri,
        ?Json $body,
        string $message
    ): void {
        $this->expectException(ClientException::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('`401 Unauthorized` response:' . PHP_EOL . $message);

        $this->prepareDatabase();

        TestClient::createWithApiDocValidation($bearer, $method, $uri, $body);
    }


    protected function tearDown(): void
    {
        if ($this->name() === 'testCanThrowServerErrorExceptionWhileSavingSuccess') {
            $this->integrationDatabase->recreate(TableName::wordsSuccessCounterToUsers);
        }

        $this->integrationDatabase->truncate(TableName::wordsSuccessCounterToUsers);
        $this->integrationDatabase->truncate(TableName::users);
        $this->integrationDatabase->truncate(TableName::words);
        $this->removeLog(Path::fromString(__DIR__ . '/../../logs'));
    }
}
