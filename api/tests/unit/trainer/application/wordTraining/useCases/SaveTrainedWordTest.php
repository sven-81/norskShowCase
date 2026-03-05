<?php

declare(strict_types=1);

namespace norsk\api\trainer\application\wordTraining\useCases;

use norsk\api\shared\domain\Id;
use norsk\api\user\domain\valueObjects\UserName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SaveTrainedWord::class)]
class SaveTrainedWordTest extends TestCase
{
    private UserName $userName;

    private Id $id;

    private SaveTrainedWord $command;


    protected function setUp(): void
    {
        $this->userName = UserName::by('someUser');
        $this->id = Id::by(12);
        $this->command = SaveTrainedWord::for($this->userName, $this->id);
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
