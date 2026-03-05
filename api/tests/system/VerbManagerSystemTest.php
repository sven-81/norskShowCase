<?php

declare(strict_types=1);

namespace norsk\api;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use norsk\api\helperTools\Removable;
use norsk\api\infrastructure\config\DbConfig;
use norsk\api\infrastructure\config\Path;
use norsk\api\infrastructure\persistence\GenericSqlStatement;
use norsk\api\infrastructure\persistence\TableName;
use norsk\api\shared\application\Json;
use norsk\api\tests\stubs\TestClient;
use norsk\api\tests\stubs\VirtualTestDatabase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
class VerbManagerSystemTest extends TestCase
{
    use Removable;

    private const string GET_POST_VERBS = 'manage/verbs';

    private const string CHANGE = 'manage/verbs/';

    private VirtualTestDatabase $integrationDatabase;

    private string $uriGetVerbs;

    private string $uriCreateVerb;

    private string $uriEditVerb;

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

        $this->uriGetVerbs = self::GET_POST_VERBS;
        $this->uriCreateVerb = self::GET_POST_VERBS;
        $this->uriEditVerb = self::CHANGE;
        $this->methodGet = 'GET';
        $this->methodPost = 'POST';
        $this->methodPut = 'PUT';
        $this->methodDelete = 'DELETE';

        $this->bearerPathClient = __DIR__ . '/resources/jwts/userClient.jwt';
        $this->bearerPathManager = __DIR__ . '/resources/jwts/ottoActiveManager.jwt';

        $this->body = Json::fromString(
            '{"german":"laufen","norsk":"løpe","norskPresent":"løper",'
            . '"norskPast":"løp","norskPastPerfect":"har løpt"}'
        );
    }


    public static function getInvalidMethods(): array
    {
        return [
            'PUT@viewAndCreate' => ['PUT', self::GET_POST_VERBS],
            'PATCH@viewAndCreate' => ['PATCH', self::GET_POST_VERBS],
            'DELETE@viewAndCreate' => ['DELETE', self::GET_POST_VERBS],
            'GET@change' => ['GET', self::CHANGE . '1'],
            'PATCH@change' => ['PATCH', self::CHANGE . '1'],
            'POST@change' => ['POST', self::CHANGE . '1'],
        ];
    }


    public static function getMethodsForChanging(): array
    {
        return [
            'delete' => ['DELETE', self::CHANGE, null],
            'edit' => [
                'PUT',
                self::CHANGE,
                Json::fromString(
                    '{"german":"laufen","norsk":"løpe","norskPresent":"løper",'
                    . '"norskPast":"løp","norskPastPerfect":"har løpt"}'
                ),
            ],
        ];
    }


    public static function getInvalidManager(): array
    {
        $tokenDir = __DIR__ . '/resources/jwts/';
        $body = Json::fromString(
            '{"german":"laufen","norsk":"løpe","norskPresent":"løper",'
            . '"norskPast":"løp","norskPastPerfect":"har løpt"}'
        );

        return [
            'activeUser@viewing' => [
                $tokenDir . 'heinzActiveUser.jwt',
                'GET',
                self::GET_POST_VERBS,
                null,
                '{"message":"Unauthorized: No rights for managing words or verbs"}',
            ],
            'inactiveUser@viewing' => [
                $tokenDir . 'lenaInactiveUser.jwt',
                'GET',
                self::GET_POST_VERBS,
                null,
                '{"message":"Unauthorized: No rights for managing words or verbs"}',
            ],
            'inactiveManager@viewing' => [
                $tokenDir . 'peterInactiveManager.jwt',
                'GET',
                self::GET_POST_VERBS,
                null,
                '{"message":"Unauthorized: Current user is no active manager"}',
            ],
            'activeUser@creating' => [
                $tokenDir . 'heinzActiveUser.jwt',
                'POST',
                self::GET_POST_VERBS,
                $body,
                '{"message":"Unauthorized: No rights for managing words or verbs"}',
            ],
            'inactiveUser@creating' => [
                $tokenDir . 'lenaInactiveUser.jwt',
                'POST',
                self::GET_POST_VERBS,
                $body,
                '{"message":"Unauthorized: No rights for managing words or verbs"}',
            ],
            'inactiveManager@creating' => [
                $tokenDir . 'peterInactiveManager.jwt',
                'POST',
                self::GET_POST_VERBS,
                $body,
                '{"message":"Unauthorized: Current user is no active manager"}',
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
                '{"message":"Unauthorized: No rights for managing words or verbs"}',
            ],
            'inactiveManager@editing' => [
                $tokenDir . 'peterInactiveManager.jwt',
                'PUT',
                self::CHANGE . '1',
                $body,
                '{"message":"Unauthorized: Current user is no active manager"}',
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
                '{"message":"Unauthorized: No rights for managing words or verbs"}',
            ],
            'inactiveManager@deleting' => [
                $tokenDir . 'peterInactiveManager.jwt',
                'DELETE',
                self::CHANGE . '1',
                null,
                '{"message":"Unauthorized: Current user is no active manager"}',
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

        $sqlVerbs = file_get_contents(__DIR__ . '/resources/queries/initialVerbs.sql');
        $this->integrationDatabase->insertInitialEntryToAvoidFailing($sqlVerbs);
    }


    public function testCanThrowServerErrorExceptionIfDatabaseIsEmptyWhileGettingVerbs(): void
    {
        $this->expectException(ServerException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('500 Internal Server Error');

        $sqlUsers = file_get_contents(__DIR__ . '/resources/queries/initialUsers.sql');
        $this->integrationDatabase->insertInitialEntryToAvoidFailing($sqlUsers);

        TestClient::createWithApiDocValidation($this->bearerPathManager, $this->methodGet, $this->uriGetVerbs);
    }


    public function testCanThrowServerErrorExceptionWhileEditingVerb(): void
    {
        $this->expectException(ServerException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('500 Internal Server Error');

        $this->prepareDatabase();

        TestClient::createWithApiDocValidation(
            $this->bearerPathManager,
            $this->methodPut,
            $this->uriEditVerb . '1',
            Json::encodeFromArray([])
        );
    }


    #[DataProvider('getMethodsForChanging')]
    public function testCanThrow404IfVerbIsNotFoundForChanging(
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

        TestClient::createWithApiDocValidation($bearer, $this->methodGet, $this->uriGetVerbs);
    }


    public function testCanThrowServerErrorExceptionWhileSavingSuccess(): void
    {
        $this->expectException(ServerException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('500 Internal Server Error');

        $this->prepareDatabase();
        $sql = GenericSqlStatement::create(
            'ALTER TABLE `verbsSuccessCounterToUsers` DROP COLUMN `successCounter`;'
        );
        $this->integrationDatabase->alter($sql);

        TestClient::createWithApiDocValidation(
            $this->bearerPathManager,
            $this->methodPut,
            $this->uriEditVerb . '1',
            Json::encodeFromArray([])
        );
    }


    public function testCanRouteManagerToGetAllVerbsToTrain(): void
    {
        $this->prepareDatabase();
        $response = TestClient::createWithApiDocValidation(
            $this->bearerPathManager,
            $this->methodGet,
            $this->uriGetVerbs
        );
        $responseJson = Json::fromString((string)$response->getBody());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $expectedJson = __DIR__ . '/resources/json/verbsToTrain.json';
        $this->assertJsonStringEqualsJsonFile($expectedJson, $responseJson->asString());
    }


    public function testCanRouteManagerToEditVerb(): void
    {
        $this->prepareDatabase();

        $response = TestClient::createWithApiDocValidation(
            $this->bearerPathManager,
            $this->methodPut,
            $this->uriEditVerb . '1',
            $this->body
        );
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals('No Content', $response->getReasonPhrase());
    }


    public function testCanRouteManagerToDeleteVerb(): void
    {
        $this->prepareDatabase();
        $response = TestClient::createWithApiDocValidation(
            $this->bearerPathManager,
            $this->methodDelete,
            $this->uriEditVerb . '1'
        );
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
    }


    public function testCanRouteManagerToCreateVerb(): void
    {
        $this->prepareDatabase();

        $response = TestClient::createWithApiDocValidation(
            $this->bearerPathManager,
            $this->methodPost,
            $this->uriCreateVerb,
            $this->body
        );
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('Created', $response->getReasonPhrase());
    }


    #[DataProvider('getInvalidManager')]
    public function testCanThrowExceptionIfUserIsInvalidToGetVerbToTrain(
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
            $this->integrationDatabase->recreate(TableName::verbsSuccessCounterToUsers);
        }

        $this->integrationDatabase->truncate(TableName::verbsSuccessCounterToUsers);
        $this->integrationDatabase->truncate(TableName::users);
        $this->integrationDatabase->truncate(TableName::verbs);
        $this->removeLog(Path::fromString(__DIR__ . '/../../logs'));
    }
}
