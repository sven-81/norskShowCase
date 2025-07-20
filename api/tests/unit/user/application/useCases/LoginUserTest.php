<?php

declare(strict_types=1);

namespace norsk\api\user\application\useCases;

use norsk\api\shared\infrastructure\http\request\Payload;
use norsk\api\user\domain\exceptions\ParameterMissingException;
use norsk\api\user\domain\valueObjects\InputPassword;
use norsk\api\user\domain\valueObjects\UserName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(LoginUser::class)]
class LoginUserTest extends TestCase
{

    private LoginUser $user;

    private InputPassword $password;

    private UserName $userName;


    protected function setUp(): void
    {
        $this->userName = UserName::by('smurf');
        $this->password = InputPassword::by('shhhhhhhhhhhhhhhhhhhhhhhhhh!!');

        $payload = $this->createMock(Payload::class);
        $payload->method('asArray')
            ->willReturn(
                [
                    'username' => $this->userName->asString(),
                    'password' => $this->password->asString(),
                ]
            );
        $this->user = LoginUser::by($payload);
    }


    public function testCanGetUserName(): void
    {
        self::assertEquals($this->userName, $this->user->getUserName());
    }


    public function testCanGetPassword(): void
    {
        self::assertEquals($this->password, $this->user->getPassword());
    }


    #[DataProvider('missingFieldProvider')]
    public function testThrowsExceptionIfFieldIsMissing(array $payloadArray
    ): void {
        $payload = $this->createMock(Payload::class);
        $payload->method('asArray')
            ->willReturn($payloadArray);

        $this->expectException(ParameterMissingException::class);

        LoginUser::by($payload);
    }


    public static function missingFieldProvider(): array
    {
        return [
            'missing username' => [['password' => 'shhhhhhhhhhhhhhhhhhhhhhhhhh!!',],],
            'missing password' => [['username' => 'smurf',],],
            'empty username' => [['username' => '', 'password' => 'shhhhhhhhhhhhhhhhhhhhhhhhhh!!',],],
            'empty password' => [['username' => 'smurf', 'password' => '',],],
        ];
    }

}
