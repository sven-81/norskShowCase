<?php

declare(strict_types=1);

namespace norsk\api\helperTools;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slim\Routing\Route;
use Slim\Routing\RouteParser;
use Slim\Routing\RoutingResults;

class RouteMockHelper extends TestCase
{
    private const string NEEDED_NAME = 'stupidNameForTestCase';


    public static function createRouteParserMock(): MockObject
    {
        return (new self(self::NEEDED_NAME))->createMock(RouteParser::class);
    }


    public static function createRoutingResultsMock(): MockObject
    {
        return (new self(self::NEEDED_NAME))->createMock(RoutingResults::class);
    }


    public static function createRouteMock(): MockObject
    {
        return (new self(self::NEEDED_NAME))->createMock(Route::class);
    }
}
