<?php

declare(strict_types=1);

namespace norsk\api\app\response;

use InvalidArgumentException;

class Url
{

    private function __construct(private readonly string $url)
    {
    }


    public static function by(string $url): self
    {
        self::ensureIsValidUrl($url);

        return new self($url);
    }


    private static function ensureIsValidUrl(string $url): void
    {
        if (false === filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('The given URL is not a valid URL: ' . $url);
        }
    }


    public function asString(): string
    {
        return $this->url;
    }
}
