<?php

declare(strict_types=1);

namespace norsk\api\user;

use norsk\api\shared\infrastructure\http\response\ResponseCode;
use norsk\api\user\domain\exceptions\CredentialsAreInvalidException;
use norsk\api\user\domain\exceptions\PasswordIsInvalidException;
use norsk\api\user\domain\valueObjects\InputPassword;
use norsk\api\user\domain\valueObjects\PasswordHash;
use norsk\api\user\domain\valueObjects\PasswordVector;
use norsk\api\user\domain\valueObjects\Salt;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(PasswordHash::class)]
class PasswordHashTest extends TestCase
{
    private PasswordVector $vectorStub;

    private Salt $salt;

    private string $password;


    public static function invalidPlainPasswords(): array
    {
        return [
            [' ', 'Password name cannot be empty'],
            ['', 'Password name cannot be empty'],
        ];
    }


    public function testCanBeUsedAsPlainString(): void
    {
        self::assertSame($this->password, PasswordHash::by($this->password)->asHashString());
    }


    public function testThrowsExceptionIfPasswordIsTooShort(): void
    {
        $this->expectExceptionObject(
            new PasswordIsInvalidException(
                'Password is too short',
                ResponseCode::unprocessable->value
            )
        );

        $inputStub = $this->createStub(InputPassword::class);
        $inputStub->method('asString')
            ->willReturn('tooShort');

        PasswordHash::hashBy($inputStub, $this->vectorStub);
    }


    #[DataProvider('invalidPlainPasswords')]
    public function testThrowsExceptionIfPlainPasswordHashIsNotValid(
        string $invalidPassword,
        string $exceptionMessage
    ): void {
        $this->expectExceptionObject(
            new PasswordIsInvalidException($exceptionMessage, ResponseCode::unprocessable->value)
        );

        PasswordHash::by($invalidPassword);
    }


    public function testCanGetPasswordHashAsStringFromPlainPasswordAndSalt(): void
    {
        $vectorMock = $this->createMock(PasswordVector::class);
        $vectorMock->expects($this->once())
            ->method('getSalt')
            ->willReturn($this->salt);
        $hash = PasswordHash::hashBy(InputPassword::by($this->password), $vectorMock);

        self::assertSame(60, strlen($hash->asHashString()));
        self::assertStringStartsWith('$2y$12$', $hash->asHashString());
    }


    public function testCanValidatedInputPasswordWithSaltAndPepper(): void
    {
        $vectorMock = $this->createMock(PasswordVector::class);
        $vectorMock->expects($this->once())
            ->method('getSalt')
            ->willReturn(Salt::by('de3e535ef9b8bd12a0c4d6a2547a56f2aadc352a269ef8ccd5bef9c0307f2d98'));

        $hash = PasswordHash::byValidatedInputPassword(
            InputPassword::by($this->password),
            $vectorMock,
            PasswordHash::by('$2y$10$c7oMpG/2GArBg1tbIKaL/.lsGEChOZt2dQj1CoxObq6946X3vhjN.')
        );

        self::assertSame(60, strlen($hash->asHashString()));
        self::assertStringStartsWith('$2y$10$', $hash->asHashString());
    }


    public function testThrowsExceptionIfValidatedInputPasswordDoesNotMatchStoredPassword(): void
    {
        $this->expectExceptionObject(new CredentialsAreInvalidException());

        $vectorMock = $this->createMock(PasswordVector::class);
        $vectorMock->expects($this->once())
            ->method('getSalt')
            ->willReturn($this->salt);

        PasswordHash::byValidatedInputPassword(
            InputPassword::by(
                $this->password
            ),
            $vectorMock,
            PasswordHash::by('otherPassword')
        );
    }


    protected function setUp(): void
    {
        $this->salt = Salt::init();
        $this->salt->generate();
        $this->vectorStub = $this->createStub(PasswordVector::class);
        $this->password = 'abc4567890123';
    }
}
