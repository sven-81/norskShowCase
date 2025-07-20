<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\routing;

use norsk\api\trainer\infrastructure\web\controller\WordTrainer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ControllerName::class)]
class ControllerNameTest extends TestCase
{

    public function testCanUseNameAsString(): void
    {
        $wordTrainerMock = $this->createMock(WordTrainer::class);
        $controllerName = ControllerName::of($wordTrainerMock)->asString();

        self::assertStringContainsString('WordTrainer', $controllerName);
    }
}
