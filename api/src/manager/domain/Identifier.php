<?php

declare(strict_types=1);

namespace norsk\api\manager\domain;

use norsk\api\shared\domain\German;
use norsk\api\shared\domain\Id;
use norsk\api\shared\domain\Norsk;

readonly class Identifier
{
    private function __construct(private string $identifier)
    {
    }


    public static function fromId(Id $id): self
    {
        return new self($id->asString());
    }


    public static function fromVocabulary(German $german, Norsk $norsk): self
    {
        return new self($german->asString() . ' | ' . $norsk->asString());
    }


    public function asMessageString(): string
    {
        return $this->addPrefixToId() . $this->asString();
    }


    private function addPrefixToId(): string
    {
        if (is_numeric($this->identifier)) {
            return 'id: ';
        }

        return '';
    }


    private function asString(): string
    {
        return $this->identifier;
    }
}
