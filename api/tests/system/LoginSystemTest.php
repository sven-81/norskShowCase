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
use Psr\Http\Message\ResponseInterface;

#[CoversNothing]
class LoginSystemTest extends TestCase
{
    use Removable;

    private VirtualTestDatabase $integrationDatabase;

    private string $uri;

    private string $method;

    private string $bearerPath;


    protected function setUp(): void
    {
        $dbConfig = DbConfig::fromPath(
            Path::fromString(__DIR__ . '/resources/config/mySqlConfig.ini')
        );
        $this->integrationDatabase = VirtualTestDatabase::create($dbConfig);

        $this->method = 'POST';
        $this->uri = 'user';
        $this->bearerPath = __DIR__ . '/resources/jwts/userClient.jwt';
    }


    public static function getInvalidMethods(): array
    {
        return [
            'GET' => ['GET'],
            'PUT' => ['PUT'],
            'PATCH' => ['PATCH'],
            'DELETE' => ['DELETE'],
        ];
    }


    public static function getActiveUsers(): array
    {
        $token = file_get_contents(__DIR__ . '/resources/jwts/heinzActiveUserExpired.jwt');
        $expectedJsonHeinz = Json::fromString(
            '{"login":true,"username":"heinz","firstName":"heinz","lastName":"heribert",'
            . '"token":"' . $token . '"}'
        );
        $expectedJsonOtto = Json::fromString(
            '{"login":true,"username":"Otto","firstName":"Otto","lastName":"Motto",'
            . '"token":"' . $token . '"}'
        );

        return [
            'activeUser' => ['heinz', $expectedJsonHeinz,],
            'activeManager' => ['Otto', $expectedJsonOtto,],
        ];
    }


    public static function getNonActiveUsers(): array
    {
        return [
            'inactiveUser' => ['lena', 403],
            'inactiveManager' => ['peter', 403],
            'nonExisting' => ['nobody', 401],
        ];
    }


    #[DataProvider('getInvalidMethods')]
    public function testCanThrowMethodNotAllowException(string $method): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionCode(405);
        $this->expectExceptionMessage('405 Method Not Allowed');

        TestClient::createWithoutApiDocValidation($this->bearerPath, $method, $this->uri);
    }


    #[DataProvider('getActiveUsers')]
    public function testCanRouteUserToLoginWithPositiveResponse(string $userName, Json $expectedJson): void
    {
        $this->prepareDatabase();

        $requestBody = Json::fromString(
            '{ "username": "' . $userName . '", "password": "myVerySecretlySecret" }'
        );


        $response = TestClient::createWithApiDocValidation($this->bearerPath, $this->method, $this->uri, $requestBody);
        $this->rewindBodyToGetUneditedResponseBecauseMiddlewareIsEditingItToEmpty($response);
        $responseJson = Json::fromString($response->getBody()->getContents());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());

        $responseArray = $responseJson->asDecodedJson();
        $this->assertArrayIsIdenticalToArrayIgnoringListOfKeys(
            $expectedJson->asDecodedJson(),
            $responseArray,
            ['token']
        );
        $this->assertStringStartsWith(
            'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.'
            . 'eyJzdWIiOiJub3JzayBhcHAiLCJhdWQiOiJOb3JzayBDbGllbnQiLCJpYXQiOjE3',
            $responseArray['token']
        );
    }


    private function prepareDatabase(): void
    {
        $sql = file_get_contents(__DIR__ . '/resources/queries/initialUsers.sql');
        $this->integrationDatabase->insertInitialEntryToAvoidFailing($sql);
    }


    #[DataProvider('getNonActiveUsers')]
    public function testCanRouteUserToLoginWithNegativeResponse(
        string $userName,
        int $expectedCode,
    ): void {
        $this->expectException(ClientException::class);
        $message = $this->getErrorMessage($expectedCode);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode($expectedCode);

        $this->prepareDatabase();

        $requestBody = Json::fromString(
            '{ "username": "' . $userName . '", "password": "myVerySecretlySecret" }'
        );

        TestClient::createWithApiDocValidation($this->bearerPath, $this->method, $this->uri, $requestBody);
    }


    private function getErrorMessage(int $expectedCode): string
    {
        if ($expectedCode === 403) {
            return '`403 Forbidden` response:'
                   . PHP_EOL . '{"message":"Forbidden: user is not active"}';
        }

        return '`401 Unauthorized` response:'
               . PHP_EOL . '{"message":"Unauthorized: Cannot verify credentials"}';
    }


    public function testCanThrowBadRequestException(): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage(
            '`400 Bad Request` response:' . PHP_EOL .
            '{"message":"No request body"}'
        );

        TestClient::createWithoutApiDocValidation($this->bearerPath, $this->method, $this->uri);
    }


    private function rewindBodyToGetUneditedResponseBecauseMiddlewareIsEditingItToEmpty(
        ResponseInterface $response
    ): void {
        $response->getBody()->rewind();
    }


    public function testCanThrowServerErrorException(): void
    {
        $this->expectException(ServerException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('500 Internal Server Error');

        $sql = GenericSqlStatement::create(
            'ALTER TABLE `users` DROP COLUMN `password_hash`;'
        );
        $this->integrationDatabase->alter($sql);

        $requestBody = Json::fromString(
            '{ "username": "heinz", "password": "myVerySecretlySecret" }'
        );

        TestClient::createWithApiDocValidation($this->bearerPath, $this->method, $this->uri, $requestBody);
    }


    protected function tearDown(): void
    {
        if ($this->name() === 'testCanThrowServerErrorException') {
            $this->integrationDatabase->recreate(TableName::users);
        }

        $this->integrationDatabase->truncate(TableName::users);
        $this->removeLog(Path::fromString(__DIR__ . '/../../logs'));
    }
}
