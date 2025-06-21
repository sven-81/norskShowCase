<?php

declare(strict_types=1);

namespace norsk\api\user;

use InvalidArgumentException;
use norsk\api\app\response\ResponseCode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InputPassword::class)]
class InputPasswordTest extends TestCase
{
    public function testCanCreateInputPasswordWithValidPassword(): void
    {
        $password = 'validPassword123';
        $inputPassword = InputPassword::by($password);

        $this->assertSame('validPassword123', $inputPassword->asString());
    }


    public function testCannotCreateInputPasswordWithEmptyString(): void
    {
        $this->expectExceptionObject(
            new InvalidArgumentException(
                'The password must be at least 12 characters long.',
                ResponseCode::unprocessable->value
            )
        );

        InputPassword::by('');
    }


    public function testCannotCreateInputPasswordWithWhitespaceOnly(): void
    {
        $this->expectExceptionObject(
            new InvalidArgumentException(
                'The password must be at least 12 characters long.',
                ResponseCode::unprocessable->value
            )
        );

        InputPassword::by('   ');
    }


    public function testCannotCreateInputPasswordWithInvalidChars(): void
    {
        $this->expectExceptionObject(
            new InvalidArgumentException(
                'Password contains invalid characters: \' or &.',
                ResponseCode::unprocessable->value
            )
        );

        InputPassword::by('D\'oh123456789!');
    }


    public function testCanCreateInputPasswordWithTrimmedWhitespace(): void
    {
        $password = '   validTrimmedPassword   ';
        $inputPassword = InputPassword::by($password);

        $this->assertSame('validTrimmedPassword', $inputPassword->asString());
    }
}
