<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\persistence;

use norsk\api\infrastructure\persistence\AffectedRows;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(AffectedRows::class)]
class AffectedRowsTest extends TestCase
{
    public function testReturnsTrueIfAffectedRowsAreNotAtLeastOne(): void
    {
        self::assertTrue(AffectedRows::fromInt(0)->notAtLeastOne());
    }


    public function testReturnsFalseIfAffectedRowsAreAtLeastOne(): void
    {
        self::assertFalse(AffectedRows::fromInt(1)->notAtLeastOne());
    }


    public function testThrowsExceptionIfAffectedRowsIsNegative(): void
    {
        $this->expectExceptionObject(
            new RuntimeException('An error has occurred during executing database query')
        );

        AffectedRows::fromInt(-1)->notAtLeastOne();
    }
}
