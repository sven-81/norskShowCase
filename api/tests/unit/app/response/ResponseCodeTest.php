<?php

declare(strict_types=1);

namespace norsk\api\app\response;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResponseCode::class)]
class ResponseCodeTest extends TestCase
{
    public static function getCodes(): array
    {
        return [
            'success' => [200, ResponseCode::success->value],
            'created' => [201, ResponseCode::created->value],
            'noContent' => [204, ResponseCode::noContent->value],
            'badRequest' => [400, ResponseCode::badRequest->value,],
            'unauthorized' => [401, ResponseCode::unauthorized->value,],
            'forbidden' => [403, ResponseCode::forbidden->value,],
            'notFound' => [404, ResponseCode::notFound->value,],
            'conflict' => [409, ResponseCode::conflict->value,],
            'unprocessable' => [422, ResponseCode::unprocessable->value,],
            'serverError' => [500, ResponseCode::serverError->value,],
        ];
    }


    #[DataProvider('getCodes')]
    public function testEnsureTableNames($expected, $givenCode): void
    {
        self::assertEquals($expected, $givenCode);
    }
}
