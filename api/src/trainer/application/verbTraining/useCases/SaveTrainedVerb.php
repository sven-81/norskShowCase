<?php

declare(strict_types=1);

namespace norsk\api\trainer\application\verbTraining\useCases;

use norsk\api\shared\domain\Id;
use norsk\api\user\domain\valueObjects\UserName;

readonly class SaveTrainedVerb
{
    private function __construct(
        private UserName $userName,
        private Id $id
    ) {
    }


    public static function for(UserName $userName, Id $id): self
    {
        return new self($userName, $id);
    }


    public function getUserName(): UserName
    {
        return $this->userName;
    }


    public function getId(): Id
    {
        return $this->id;
    }
}
