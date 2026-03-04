<?php

declare(strict_types=1);

namespace norsk\api\trainer\application\wordTraining\useCases;

use norsk\api\user\domain\valueObjects\UserName;

readonly class GetWordToTrain
{
    private function __construct(private UserName $userName)
    {
    }


    public static function for(UserName $userName): self
    {
        return new self($userName);
    }


    public function getUserName(): UserName
    {
        return $this->userName;
    }
}
