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
class WordTrainerSystemTest extends TestCase
{
    use Removable;

    private const string GET_WORDS = 'train/words';
    private const string SAVE_SUCCESS = 'train/words/';

    private VirtualTestDatabase $integrationDatabase;

    private string $uriGetWords;

    private string $uriSaveSuccess;

    private string $methodPatch;

    private string $methodGet;

    private string $bearerPathClient;


    public static function getInvalidMethods(): array
    {
        return [
            'PUT@words' => ['PUT', self::GET_WORDS],
            'PATCH@words' => ['PATCH', self::GET_WORDS],
            'POST@words' => ['POST', self::GET_WORDS],
            'DELETE@words' => ['DELETE', self::GET_WORDS],
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

        TestClient::createWithoutApiDocValidation($this->bearerPathClient, $method, $uri);
    }


    private function prepareDatabase(): void
    {
        $sqlUsers = file_get_contents(__DIR__ . '/resources/queries/initialUsers.sql');
        $this->integrationDatabase->insertInitialEntryToAvoidFailing($sqlUsers);

        $sqlWords = file_get_contents(__DIR__ . '/resources/queries/initialWords.sql');
        $this->integrationDatabase->insertInitialEntryToAvoidFailing($sqlWords);
    }


    public function testCanThrowServerErrorExceptionWhileGettingWords(): void
    {
        $this->expectException(ServerException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('500 Internal Server Error');

        TestClient::createWithApiDocValidation($this->bearerPathClient, $this->methodGet, $this->uriGetWords);
    }


    public function testCanThrow404IfWordIsNotFoundForSavingSuccess(): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionCode(404);
        $this->expectExceptionMessage(
            '`404 Not Found` response:' . PHP_EOL .
            '{"message":"No record found in database for wordId: 11"}'
        );

        TestClient::createWithApiDocValidation(
            $this->bearerPathClient,
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

        TestClient::createWithApiDocValidation($bearer, $this->methodPatch, $this->uriSaveSuccess . '1');
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

        $bearer = __DIR__ . '/resources/jwts/heinzActiveUser.jwt';

        TestClient::createWithApiDocValidation($bearer, $this->methodPatch, $this->uriSaveSuccess . '1');
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
        $bearer = __DIR__ . '/resources/jwts/heinzActiveUser.jwt';

        TestClient::createWithoutApiDocValidation($bearer, $this->methodPatch, $this->uriSaveSuccess . 'ab');
    }


    #[DataProvider('getActiveUsers')]
    public function testCanRouteUserToGetAllWordsToTrain(string $bearer): void
    {
        $this->prepareDatabase();
        $response = TestClient::createWithApiDocValidation($bearer, $this->methodGet, $this->uriGetWords);
        $responseJson = Json::fromString((string)$response->getBody());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
        $responseArray = $responseJson->asDecodedJson();
        $this->assertArrayHasKey('id', $responseArray);
        $this->assertArrayHasKey('german', $responseArray);
        $this->assertArrayHasKey('norsk', $responseArray);
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
    public function testCanThrowExceptionIfUserIsInvalidToGetWordToTrain(string $bearer): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage(
            '`401 Unauthorized` response:' . PHP_EOL .
            '{"message":"Unauthorized: Cannot verify credentials"}'
        );

        $this->prepareDatabase();

        TestClient::createWithApiDocValidation($bearer, $this->methodGet, $this->uriGetWords);
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

        TestClient::createWithApiDocValidation($bearer, $this->methodPatch, $this->uriSaveSuccess . '1');
    }


    protected function setUp(): void
    {
        $dbConfig = DbConfig::fromPath(
            Path::fromString(__DIR__ . '/resources/config/mySqlConfig.ini')
        );
        $this->integrationDatabase = VirtualTestDatabase::create($dbConfig);

        $this->uriGetWords = self::GET_WORDS;
        $this->uriSaveSuccess = self::SAVE_SUCCESS;
        $this->methodPatch = 'PATCH';
        $this->methodGet = 'GET';
        $this->bearerPathClient = __DIR__ . '/resources/jwts/userClient.jwt';
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
