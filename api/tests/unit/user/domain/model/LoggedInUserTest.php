<?php

declare(strict_types=1);

namespace norsk\api\user;

use norsk\api\shared\application\Json;
use norsk\api\user\domain\model\LoggedInUser;
use norsk\api\user\domain\model\ValidatedUser;
use norsk\api\user\domain\valueObjects\FirstName;
use norsk\api\user\domain\valueObjects\LastName;
use norsk\api\user\domain\valueObjects\UserName;
use norsk\api\user\infrastructure\identityAccessManagement\jwt\JsonWebToken;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LoggedInUser::class)]
class LoggedInUserTest extends TestCase
{
    private LoggedInUser $loggedInUser;

    private UserName $userName;


    public function testCanGetUserName(): void
    {
        self::assertSame($this->userName, $this->loggedInUser->getUsername());
    }


    public function testAsBodyJsonReturnsCorrectJson(): void
    {
        $expectedArray = [
            'login' => true,
            'username' => 'testuser',
            'firstName' => 'Karl',
            'lastName' => 'Kopf',
            'token' => 'fakeToken',
            'tokenType' => 'Bearer',
            'expiresIn' => 7200,
        ];

        $expectedJson = Json::encodeFromArray($expectedArray);
        $this->assertEquals($expectedJson, $this->loggedInUser->asBodyJson());
    }


    protected function setUp(): void
    {
        $validatedUserMock = $this->createMock(ValidatedUser::class);
        $this->userName = UserName::by('testuser');
        $validatedUserMock->method('getUsername')
            ->willReturn($this->userName);
        $validatedUserMock->method('getFirstName')
            ->willReturn(FirstName::by('Karl'));
        $validatedUserMock->method('getLastName')
            ->willReturn(LastName::by('Kopf'));

        $jwTokenMock = $this->createMock(JsonWebToken::class);
        $jwTokenMock->method('asString')
            ->willReturn('fakeToken');

        $this->loggedInUser = LoggedInUser::by(
            $validatedUserMock,
            $jwTokenMock
        );
    }
}
