<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\response;

use norsk\api\shared\infrastructure\http\response\ResponseHeaders;
use norsk\api\shared\infrastructure\http\response\Url;
use norsk\api\tests\provider\TestHeader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResponseHeaders::class)]
class ResponseHeadersTest extends TestCase
{
    use TestHeader;
    public function testCanBeUsedAsArray(): void
    {
        $url = Url::by('http://foo');
        self::assertSame($this->getTestHeaderAsArray($url),
            ResponseHeaders::create($url)->asArray()
        );
    }
}
