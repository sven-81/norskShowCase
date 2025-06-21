<?php

declare(strict_types=1);

namespace norsk\api\manager;

use norsk\api\app\request\Payload;
use norsk\api\shared\Id;
use norsk\api\shared\Json;

readonly class Identifier
{
    private function __construct(private string $identifier)
    {
    }


    public static function fromId(Id $id): self
    {
        return new self($id->asString());
    }


    public static function fromPayload(Payload $payload): self
    {
        return new self(Json::asEscaped($payload)->asString());
    }


    public function asMessageString(): string
    {
        return $this->addPrefixToId() . $this->asString();
    }


    private function escapeOuterJsonQuotes(string $jsonString): string
    {
        return preg_replace('#^(.*)(\"{)(.*)(}")$#', '$1\"{$3}\"', $jsonString);
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
        if (!is_numeric($this->identifier)) {
            return $this->escapeOuterJsonQuotes($this->identifier);
        }

        return $this->identifier;
    }
}
