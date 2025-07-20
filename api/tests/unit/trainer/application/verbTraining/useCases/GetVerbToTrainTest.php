<?php

declare(strict_types=1);

namespace norsk\api\trainer\application\verbTraining\useCases;

use norsk\api\user\domain\valueObjects\UserName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GetVerbToTrain::class)]
class GetVerbToTrainTest extends TestCase
{
    private UserName $userName;


    protected function setUp(): void
    {
        $this->userName = UserName::by('someUser');
    }


    public function testCanGetUserName(): void
    {
        self::assertSame($this->userName, GetVerbToTrain::for($this->userName)->getUserName());
    }
}
