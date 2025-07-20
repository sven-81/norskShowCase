<?php

declare(strict_types=1);

namespace norsk\api\trainer\application\verbTraining\useCases;

use norsk\api\shared\domain\Id;
use norsk\api\user\domain\valueObjects\UserName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SaveTrainedVerb::class)]
class SaveTrainedVerbTest extends TestCase
{
    private UserName $userName;

    private Id $id;

    private SaveTrainedVerb $command;


    protected function setUp(): void
    {
        $this->userName = UserName::by('someUser');
        $this->id = Id::by(12);
        $this->command = SaveTrainedVerb::for($this->userName, $this->id);
    }


    public function testCanGetUserName(): void
    {
        self::assertSame($this->userName, $this->command->getUserName());
    }


    public function testCanGetId(): void
    {
        self::assertSame($this->id, $this->command->getId());
    }
}
