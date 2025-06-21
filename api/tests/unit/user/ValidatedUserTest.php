<?php

declare(strict_types=1);

namespace norsk\api\user;

use InvalidArgumentException;
use norsk\api\app\persistence\SqlResult;
use norsk\api\app\response\ResponseCode;
use norsk\api\user\exceptions\CredentialsAreInvalidException;
use norsk\api\user\exceptions\NoActiveUserException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(ValidatedUser::class)]
class ValidatedUserTest extends TestCase
{
    private ValidatedUser $user;

    private UserName $userName;

    private FirstName $firstName;

    private LastName $lastName;

    private array $array;

    private MockObject|SqlResult $sqlResultMock;

    private InputPassword $givenPassword;

    private Pepper $pepper;

    private Salt $salt;


    public static function getMissingKeys(): array
    {
        return [
            'username' => ['username'],
            'firstname' => ['firstname'],
            'lastname' => ['lastname'],
            'password_hash' => ['password_hash'],
            'role' => ['role'],
        ];
    }


    public function testCanGetUserName(): void
    {
        self::assertEquals($this->userName, $this->user->getUserName());
    }


    public function testCanGetFirstName(): void
    {
        self::assertEquals($this->firstName, $this->user->getFirstName());
    }


    public function testCanGetLastName(): void
    {
        self::assertEquals($this->lastName, $this->user->getLastName());
    }


    public function testCanGetRole(): void
    {
        self::assertSame(Role::USER, $this->user->getRole());
    }


    public function testThrowsExceptionOnInvalidPassword(): void
    {
        $this->expectExceptionObject(new CredentialsAreInvalidException());
        $this->user = ValidatedUser::createBySqlResultAndPasswordHash(
            $this->sqlResultMock,
            InputPassword::by('somethingVeryWrong'),
            $this->pepper
        );
    }


    #[DataProvider('getMissingKeys')]
    public function testThrowsExceptionIfParameterIsMissing(string $missingKey): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('Missing field: ' . $missingKey));

        unset($this->array[0][$missingKey]);

        $invalidResultMock = $this->createMock(SqlResult::class);
        $invalidResultMock->method('asArray')
            ->willReturn($this->array);

        $this->user = ValidatedUser::createBySqlResultAndPasswordHash(
            $invalidResultMock,
            $this->givenPassword,
            $this->pepper
        );
    }


    public function testThrowsExceptionIfUserIsNotActive(): void
    {
        $this->expectExceptionObject(
            new NoActiveUserException('Forbidden: user is not active', ResponseCode::forbidden->value)
        );

        $array = [
            [
                'username' => 'someUserName',
                'firstname' => 'someFirstName',
                'lastname' => 'someLastName',
                'password_hash' => '$2y$10$2zb1NPZIFt8wjf/rddjmjup2bllCl5icWF5zdHmebMwZgEtambmvC',
                'salt' => $this->salt->asString(),
                'role' => 'user',
                'active' => 0,
            ],
        ];

        $invalidResultMock = $this->createMock(SqlResult::class);
        $invalidResultMock->method('asArray')
            ->willReturn($array);

        $this->user = ValidatedUser::createBySqlResultAndPasswordHash(
            $invalidResultMock,
            $this->givenPassword,
            $this->pepper
        );
    }


    protected function setUp(): void
    {
        $this->userName = UserName::by('someUserName');
        $this->firstName = FirstName::by('someFirstName');
        $this->lastName = LastName::by('someLastName');
        $this->salt = Salt::by('b681dc56924c1b5dc92bf97f79708fd89e84cbe128548687bb8070eb002e82b4');
        $this->givenPassword = InputPassword::by('someLoooongPassword');
        $this->pepper = Pepper::by('iwwBYerIjfYhu04X0mm5GvN4woua6yqI');

        $this->array = [
            [
                'username' => 'someUserName',
                'firstname' => 'someFirstName',
                'lastname' => 'someLastName',
                'password_hash' => '$2y$10$2zb1NPZIFt8wjf/rddjmjup2bllCl5icWF5zdHmebMwZgEtambmvC',
                'salt' => $this->salt->asString(),
                'role' => 'user',
                'active' => 1,
            ],
        ];
        $this->sqlResultMock = $this->createMock(SqlResult::class);
        $this->sqlResultMock->method('asArray')
            ->willReturn($this->array);

        $this->user = ValidatedUser::createBySqlResultAndPasswordHash(
            $this->sqlResultMock,
            $this->givenPassword,
            $this->pepper
        );
    }
}
