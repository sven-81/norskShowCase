<?php

declare(strict_types=1);

namespace norsk\api\helperTools;

use norsk\api\user\infrastructure\identityAccessManagement\jwt\JwtManagement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MockHelper extends TestCase
{
    private const string NEEDED_NAME = 'stupidNameForTestCase';


    public static function createJwtManagementMock(): MockObject
    {
        return (new self(self::NEEDED_NAME))->createMock(JwtManagement::class);
    }
}
