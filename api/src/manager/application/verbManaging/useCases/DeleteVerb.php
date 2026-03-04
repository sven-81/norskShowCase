<?php

declare(strict_types=1);

namespace norsk\api\manager\application\verbManaging\useCases;

use norsk\api\shared\domain\Id;

readonly class DeleteVerb
{
    private function __construct(private Id $id)
    {
    }


    public static function createBy(Id $id): self
    {
        return new self($id);
    }


    public function getId(): Id
    {
        return $this->id;
    }
}
