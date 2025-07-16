<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\response;

use GuzzleHttp\Psr7\Response;
use norsk\api\shared\infrastructure\http\response\ResponseHeaderApplier;
use norsk\api\shared\infrastructure\http\response\ResponseHeaders;
use norsk\api\shared\infrastructure\http\response\Url;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResponseHeaderApplier::class)]
class ResponseHeaderApplierTest extends TestCase
{

    public function testCanApplyHeader(): void
    {
        $response = new Response();

        $applier = ResponseHeaderApplier::fromHeaders(ResponseHeaders::create(Url::by('http://foo.bar')));
        $appliedResponse = $applier->apply($response);

        self::assertEquals('application/json', $appliedResponse->getHeaderLine('Content-Type'));
        self::assertEquals('http://foo.bar', $appliedResponse->getHeaderLine('Access-Control-Allow-Origin'));
    }
}
