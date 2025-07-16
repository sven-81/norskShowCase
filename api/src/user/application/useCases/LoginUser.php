<?php

declare(strict_types=1);

namespace norsk\api\user\application\useCases;

use norsk\api\shared\infrastructure\http\request\Parameter;
use norsk\api\shared\infrastructure\http\request\Payload;
use norsk\api\user\domain\exceptions\ParameterMissingException;
use norsk\api\user\domain\valueObjects\InputPassword;
use norsk\api\user\domain\valueObjects\UserName;

readonly class LoginUser
{
    private const string USERNAME = 'username';
    private const string PASSWORD = 'password';


    private function __construct(
        private UserName $userName,
        private InputPassword $password
    ) {
    }


    public static function by(Payload $payload): self
    {
        $payloadArray = $payload->asArray();
        self::ensureFieldsExists($payloadArray);

        $userName = UserName::by($payloadArray[self::USERNAME]);
        $inputPassword = InputPassword::by($payloadArray[self::PASSWORD]);

        return new self($userName, $inputPassword);
    }


    private static function ensureFieldsExists(array $payloadArray): void
    {
        foreach ([self::USERNAME, self::PASSWORD] as $field) {
            if (empty($payloadArray[$field])) {
                throw new ParameterMissingException(Parameter::by($field));
            }
        }
    }


    public function getUserName(): UserName
    {
        return $this->userName;
    }


    public function getPassword(): InputPassword
    {
        return $this->password;
    }
}
