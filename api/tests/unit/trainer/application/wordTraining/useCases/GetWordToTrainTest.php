<?php

declare(strict_types=1);

namespace norsk\api\trainer\application\wordTraining\useCases;

use norsk\api\user\domain\valueObjects\UserName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GetWordToTrain::class)]
class GetWordToTrainTest extends TestCase
{
    private UserName $userName;


    protected function setUp(): void
    {
        $this->userName = UserName::by('someUser');
    }


    public function testCanGetUserName(): void
    {
        self::assertSame($this->userName, GetWordToTrain::for($this->userName)->getUserName());
    }
}
