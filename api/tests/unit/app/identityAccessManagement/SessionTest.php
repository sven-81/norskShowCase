<?php

declare(strict_types=1);

namespace norsk\api\app\identityAccessManagement;

use InvalidArgumentException;
use norsk\api\app\request\Payload;
use norsk\api\app\response\ResponseCode;
use norsk\api\user\UserName;
use norsk\api\user\UsersReader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

use function PHPUnit\Framework\assertTrue;

#[CoversClass(Session::class)]
class SessionTest extends TestCase
{
    private const string NORSK_CLIENT = 'norsk client';

    private Session $session;

    private UsersReader|MockObject $usersReaderMock;


    public function testCanCreateSession(): void
    {
        self::assertTrue(isset($_SESSION));
        self::assertNotEmpty(session_id());
    }


    public function testCanGetUserName(): void
    {
        $_SESSION['user'] = 'mats';
        self::assertEquals('mats', $this->session::getUserName()->asString());
    }


    public function testCanGetUnsetUserName(): void
    {
        $this->expectExceptionObject(
            new InvalidArgumentException('Session user is not set.', ResponseCode::serverError->value)
        );
        $this->session::getUserName();
    }


    public function testIsValidManagerReturnsTrue(): void
    {
        $_SESSION['user'] = 'mats';
        $_SESSION['scope'] = 'is:manager';
        $this->assertTrue($this->session::isValidManager());
    }


    public function testIsValidManagerReturnsFalseIfNoUserIsSet(): void
    {
        $_SESSION['scope'] = 'is:manager';
        $this->assertFalse($this->session::isValidManager());
    }


    public function testIsValidManagerReturnsFalseIfNoScopeIsSet(): void
    {
        $_SESSION['user'] = 'mats';
        $this->assertFalse($this->session::isValidManager());
    }


    public function testIsValidManagerReturnsFalseIfNoScopeIsNotManager(): void
    {
        $_SESSION['user'] = 'mats';
        $_SESSION['scope'] = 'is:user';
        $this->assertFalse($this->session::isValidManager());
    }


    public function testCanAddScopeFromJwtContent(): void
    {
        $object = new stdClass();
        $object->scope = 'is:manager';
        $payload = Payload::by($object);

        $this->session->addJwtContent($payload, $this->usersReaderMock);

        assertTrue($_SESSION['scope'] === 'is:manager');
    }


    public function testCanAddExpirationDateFromJwtContent(): void
    {
        $object = new stdClass();
        $object->exp = 1731443747;
        $payload = Payload::by($object);

        $this->session->addJwtContent($payload, $this->usersReaderMock);

        assertTrue($_SESSION['expire'] === 1731443747);
    }


    public function testCanAddUserNameFromJwtContent(): void
    {
        $object = new stdClass();
        $object->nickname = 'mats';
        $payload = Payload::by($object);

        $this->usersReaderMock->expects($this->once())
            ->method('checkIfUserExists')
            ->with(UserName::by('mats'));

        $this->session->addJwtContent($payload, $this->usersReaderMock);

        assertTrue($_SESSION['user'] === 'mats');
    }


    public function testCanAddClientDefaultNameFromJwtContent(): void
    {
        $object = new stdClass();
        $object->nickname = self::NORSK_CLIENT;
        $payload = Payload::by($object);

        $this->usersReaderMock->expects($this->never())
            ->method('checkIfUserExists');

        $this->session->addJwtContent($payload, $this->usersReaderMock);

        assertTrue($_SESSION['user'] === self::NORSK_CLIENT);
    }


    public function testCanDestroySession(): void
    {
        $_SESSION['user'] = 'mats';

        $this->session->destroy();
        self::assertEmpty($_SESSION);
        self::assertEmpty(session_id());
    }


    protected function setUp(): void
    {
        $this->session = Session::create();
        $this->usersReaderMock = $this->createMock(UsersReader::class);
    }


    protected function tearDown(): void
    {
        if (isset($_SESSION)) {
            session_destroy();
        }
    }
}
