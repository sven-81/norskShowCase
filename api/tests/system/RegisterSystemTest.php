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
class RegisterSystemTest extends TestCase
{
    use Removable;

    private VirtualTestDatabase $integrationDatabase;

    private string $method;

    private string $uri;

    private string $bearerPath;


    public static function getInvalidMethods(): array
    {
        return [
            'GET' => ['GET'],
            'PUT' => ['PUT'],
            'PATCH' => ['PATCH'],
            'DELETE' => ['DELETE'],
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


    public function testCanRouteNewUserToRegister(): void
    {
        $requestBody = Json::fromString(
            '{ "username": "KlausiMausi", "firstName": "Karl-Klaus", '
            . '"lastName": "Tausch-Rausch", "password": "myVerySecretlySecret" }'
        );
        $response = TestClient::createWithApiDocValidation($this->bearerPath, $this->method, $this->uri, $requestBody);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('{}', $response->getBody()->getContents());
    }


    public function testCanReturnErrorOnExistingUser(): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionCode(409);
        $this->expectExceptionMessage(
            '`409 Conflict` response:' . PHP_EOL .
            '{"message":"Duplicate entry \'heinz\' for key \'PRIMARY\'"}'
        );

        $sql = file_get_contents(__DIR__ . '/resources/queries/initialUsers.sql');
        $this->integrationDatabase->insertInitialEntryToAvoidFailing($sql);

        $requestBody = Json::fromString(
            '{ "username": "heinz", "firstName": "Karl-Klaus", '
            . '"lastName": "Tausch-Rausch", "password": "myVerySecretlySecret" }'
        );

        TestClient::createWithApiDocValidation($this->bearerPath, $this->method, $this->uri, $requestBody);
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
            '{ "username": "heinz", "firstName": "Karl-Klaus", '
            . '"lastName": "Tausch-Rausch", "password": "myVerySecretlySecret" }'
        );

        TestClient::createWithApiDocValidation($this->bearerPath, $this->method, $this->uri, $requestBody);
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


    protected function setUp(): void
    {
        $dbConfig = DbConfig::fromPath(
            Path::fromString(__DIR__ . '/resources/config/mySqlConfig.ini')
        );
        $this->integrationDatabase = VirtualTestDatabase::create($dbConfig);

        $this->method = 'POST';
        $this->uri = 'user/new';

        $this->bearerPath = __DIR__ . '/resources/jwts/userClient.jwt';
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
