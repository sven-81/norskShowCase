<?php

declare(strict_types=1);

namespace norsk\api\user\application\useCases;

use InvalidArgumentException;
use norsk\api\shared\infrastructure\http\request\Payload;
use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\user\domain\valueObjects\FirstName;
use norsk\api\user\domain\valueObjects\InputPassword;
use norsk\api\user\domain\valueObjects\LastName;
use norsk\api\user\domain\valueObjects\UserName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(RegisterUser::class)]
class RegisterUserTest extends TestCase
{

    private array $payload;

    private RegisterUser $registerUser;

    private UserName $userName;

    private FirstName $firstName;

    private LastName $lastName;

    private InputPassword $password;


    protected function setUp(): void
    {
        $this->userName = UserName::by('someUserName');
        $this->firstName = FirstName::by('someFirstName');
        $this->lastName = LastName::by('someLastName');
        $this->password = InputPassword::by('someLoooongPassword');

        $this->payload = [
            'username' => 'someUserName',
            'firstName' => 'someFirstName',
            'lastName' => 'someLastName',
            'password' => 'someLoooongPassword',
        ];
        $payloadMock = $this->createMock(Payload::class);
        $payloadMock->method('asArray')
            ->willReturn($this->payload);;

        $this->registerUser = RegisterUser::by($payloadMock);
    }


    public function testCanGetUserName(): void
    {
        self::assertEquals($this->userName, $this->registerUser->getUserName());
    }


    public function testCanGetFirstName(): void
    {
        self::assertEquals($this->firstName, $this->registerUser->getFirstName());
    }


    public function testCanGetLastName(): void
    {
        self::assertEquals($this->lastName, $this->registerUser->getLastName());
    }


    public function testCanGetInputPassword(): void
    {
        self::assertEquals($this->password, $this->registerUser->getInputPassword());
    }


    public static function getMissingKeys(): array
    {
        return [
            'username' => ['username'],
            'firstName' => ['firstName'],
            'lastName' => ['lastName'],
            'password' => ['password'],
        ];
    }


    #[DataProvider('getMissingKeys')]
    public function testThrowsExceptionIfParameterIsMissing(string $missingKey): void
    {
        $this->expectExceptionObject(new InvalidArgumentException($missingKey, ResponseCode::badRequest->value));

        unset($this->payload[$missingKey]);

        $invalidPayloadMock = $this->createMock(Payload::class);
        $invalidPayloadMock->method('asArray')
            ->willReturn($this->payload);

        $this->registerUser = RegisterUser::by($invalidPayloadMock);
    }
}
