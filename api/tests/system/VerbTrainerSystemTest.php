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
class VerbTrainerSystemTest extends TestCase
{
    use Removable;

    private const string GET_VERBS = 'train/verbs';
    private const string SAVE_SUCCESS = 'train/verbs/';

    private VirtualTestDatabase $integrationDatabase;

    private string $uriGetVerbs;

    private string $uriSaveSuccess;

    private string $methodPatch;

    private string $methodGet;

    private string $clientBearerPath;

    private string $activeUserBearer;


    protected function setUp(): void
    {
        $dbConfig = DbConfig::fromPath(
            Path::fromString(__DIR__ . '/resources/config/mySqlConfig.ini')
        );
        $this->integrationDatabase = VirtualTestDatabase::create($dbConfig);

        $this->uriGetVerbs = self::GET_VERBS;
        $this->uriSaveSuccess = self::SAVE_SUCCESS;

        $this->methodPatch = 'PATCH';
        $this->methodGet = 'GET';

        $this->clientBearerPath = __DIR__ . '/resources/jwts/userClient.jwt';
        $this->activeUserBearer = __DIR__ . '/resources/jwts/heinzActiveUser.jwt';
    }


    public static function getInvalidMethods(): array
    {
        return [
            'PUT@verbs' => ['PUT', self::GET_VERBS],
            'PATCH@verbs' => ['PATCH', self::GET_VERBS],
            'POST@verbs' => ['POST', self::GET_VERBS],
            'DELETE@verbs' => ['DELETE', self::GET_VERBS],
            'GET@success' => ['GET', self::SAVE_SUCCESS . '1'],
            'PUT@success' => ['PUT', self::SAVE_SUCCESS . '1'],
            'POST@success' => ['POST', self::SAVE_SUCCESS . '1'],
            'DELETE@success' => ['DELETE', self::SAVE_SUCCESS . '1'],
        ];
    }


    public static function getActiveUsers(): array
    {
        $tokenDir = __DIR__ . '/resources/jwts/';

        return [
            'activeUser' => [$tokenDir . 'heinzActiveUser.jwt'],
            'activeManager' => [$tokenDir . 'ottoActiveManager.jwt'],
        ];
    }


    public static function getInactiveUsers(): array
    {
        $tokenDir = __DIR__ . '/resources/jwts/';

        return [
            'inactiveUser' => [$tokenDir . 'lenaInactiveUser.jwt'],
            'inactiveManager' => [$tokenDir . 'peterInactiveManager.jwt'],
        ];
    }


    #[DataProvider('getInvalidMethods')]
    public function testCanThrowMethodNotAllowException(string $method, string $uri): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionCode(405);
        $this->expectExceptionMessage('405 Method Not Allowed');

        $this->prepareDatabase();

        TestClient::createWithoutApiDocValidation($this->clientBearerPath, $method, $uri);
    }


    private function prepareDatabase(): void
    {
        $sqlUsers = file_get_contents(__DIR__ . '/resources/queries/initialUsers.sql');
        $this->integrationDatabase->insertInitialEntryToAvoidFailing($sqlUsers);

        $sqlVerbs = file_get_contents(__DIR__ . '/resources/queries/initialVerbs.sql');
        $this->integrationDatabase->insertInitialEntryToAvoidFailing($sqlVerbs);
    }


    public function testCanThrowServerErrorExceptionWhileGettingVerbs(): void
    {
        $this->prepareDatabase();
        $this->damageTableStructure();

        $this->expectException(ServerException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('500 Internal Server Error');

        TestClient::createWithApiDocValidation($this->activeUserBearer, $this->methodGet, $this->uriGetVerbs);
    }


    public function testCanThrow404IfVerbIsNotFoundForSavingSuccess(): void
    {
        $this->prepareDatabase();

        $this->expectException(ClientException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage(
            '`404 Not Found` response:' . PHP_EOL .
            '{"message":"No record found in database for verbId: 11"}'
        );

        TestClient::createWithApiDocValidation(
            $this->activeUserBearer,
            $this->methodPatch,
            $this->uriSaveSuccess . '11'
        );
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

        TestClient::createWithApiDocValidation(
            $bearer,
            $this->methodPatch,
            uri: $this->uriSaveSuccess . '1'
        );
    }


    public function testCanThrowServerErrorExceptionWhileSavingSuccess(): void
    {
        $this->expectException(ServerException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('500 Internal Server Error');

        $this->prepareDatabase();
        $this->damageTableStructure();

        TestClient::createWithApiDocValidation(
            $this->activeUserBearer,
            $this->methodPatch,
            uri: $this->uriSaveSuccess . '1'
        );
    }


    public function testCanThrowExceptionIfIdIsMalFormedWhileSavingSuccess(): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage(
            '`400 Bad Request` response:' . PHP_EOL .
            '{"message":"Id has to be numeric: ab"}'
        );

        $this->prepareDatabase();

        TestClient::createWithoutApiDocValidation(
            $this->activeUserBearer,
            $this->methodPatch,
            uri: $this->uriSaveSuccess . 'ab'
        );
    }


    #[DataProvider('getActiveUsers')]
    public function testCanRouteUserToGetAllVerbsToTrain(string $bearer): void
    {
        $this->prepareDatabase();
        $response = TestClient::createWithApiDocValidation($bearer, $this->methodGet, $this->uriGetVerbs);
        $responseJson = Json::fromString((string)$response->getBody());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $responseArray = $responseJson->asDecodedJson();
        $this->assertArrayHasKey('id', $responseArray);
        $this->assertArrayHasKey('german', $responseArray);
        $this->assertArrayHasKey('norsk', $responseArray);
        $this->assertArrayHasKey('norskPresent', $responseArray);
        $this->assertArrayHasKey('norskPast', $responseArray);
        $this->assertArrayHasKey('norskPastPerfect', $responseArray);
    }


    #[DataProvider('getActiveUsers')]
    public function testCanRouteUserToSaveSuccess(string $bearer): void
    {
        $this->prepareDatabase();
        $response = TestClient::createWithApiDocValidation($bearer, $this->methodPatch, $this->uriSaveSuccess . '1');
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals('No Content', $response->getReasonPhrase());
    }


    #[DataProvider('getInactiveUsers')]
    public function testCanThrowExceptionIfUserIsInvalidToGetVerbToTrain(string $bearer): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage(
            '`401 Unauthorized` response:' . PHP_EOL .
            '{"message":"Unauthorized: Cannot verify credentials"}'
        );

        $this->prepareDatabase();

        TestClient::createWithApiDocValidation($bearer, $this->methodGet, $this->uriGetVerbs);
    }


    #[DataProvider('getInactiveUsers')]
    public function testCanThrowExceptionIfUserIsInvalidToSaveSuccess(string $bearer): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage(
            '`401 Unauthorized` response:' . PHP_EOL .
            '{"message":"Unauthorized: Cannot verify credentials"}'
        );

        $this->prepareDatabase();

        TestClient::createWithApiDocValidation($bearer, $this->methodPatch, uri: $this->uriSaveSuccess . '1');
    }


    private function damageTableStructure(): void
    {
        $sql = GenericSqlStatement::create(
            'ALTER TABLE `verbsSuccessCounterToUsers` DROP COLUMN `successCounter`;'
        );
        $this->integrationDatabase->alter($sql);
    }


    protected function tearDown(): void
    {
        if ($this->name() === 'testCanThrowServerErrorExceptionWhileSavingSuccess'
            || $this->name() === 'testCanThrowServerErrorExceptionWhileGettingVerbs') {
            $this->integrationDatabase->recreate(TableName::verbsSuccessCounterToUsers);
        }

        $this->integrationDatabase->truncate(TableName::verbsSuccessCounterToUsers);
        $this->integrationDatabase->truncate(TableName::users);
        $this->integrationDatabase->truncate(TableName::verbs);
        $this->removeLog(Path::fromString(__DIR__ . '/../../logs'));
    }
}
