<?php

declare(strict_types=1);

namespace norsk\api\user\application\useCases;

use norsk\api\shared\infrastructure\http\request\Parameter;
use norsk\api\shared\infrastructure\http\request\Payload;
use norsk\api\user\domain\exceptions\ParameterMissingException;
use norsk\api\user\domain\valueObjects\FirstName;
use norsk\api\user\domain\valueObjects\InputPassword;
use norsk\api\user\domain\valueObjects\LastName;
use norsk\api\user\domain\valueObjects\UserName;

readonly class RegisterUser
{
    private const string USERNAME = 'username';
    private const string FIRST_NAME = 'firstName';
    private const string LAST_NAME = 'lastName';
    private const string PASSWORD = 'password';


    private function __construct(
        private UserName $userName,
        private FirstName $firstName,
        private LastName $lastName,
        private InputPassword $inputPassword,
    ) {
    }


    public static function by(Payload $payload): self
    {
        $payloadArray = $payload->asArray();
        self::ensureParameterExist($payloadArray);

        $userName = UserName::by($payloadArray[self::USERNAME]);
        $firstName = FirstName::by($payloadArray[self::FIRST_NAME]);
        $lastName = LastName::by($payloadArray[self::LAST_NAME]);
        $inputPassword = InputPassword::by($payloadArray[self::PASSWORD]);

        return new self($userName, $firstName, $lastName, $inputPassword);
    }


    private static function ensureParameterExist(array $payloadArray): void
    {
        $neededKeys = [
            self::USERNAME,
            self::FIRST_NAME,
            self::LAST_NAME,
            self::PASSWORD,
        ];

        foreach ($neededKeys as $neededKey) {
            if (!array_key_exists($neededKey, $payloadArray)) {
                throw new ParameterMissingException(Parameter::by($neededKey));
            }
        }
    }


    public function getUserName(): UserName
    {
        return $this->userName;
    }


    public function getFirstName(): FirstName
    {
        return $this->firstName;
    }


    public function getLastName(): LastName
    {
        return $this->lastName;
    }


    public function getInputPassword(): InputPassword
    {
        return $this->inputPassword;
    }
}
