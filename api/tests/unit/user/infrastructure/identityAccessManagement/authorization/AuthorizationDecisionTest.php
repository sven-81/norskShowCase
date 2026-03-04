<?php

declare(strict_types=1);

namespace norsk\api\user\infrastructure\identityAccessManagement\authorization;

use norsk\api\user\domain\model\AuthorizationResult;
use norsk\api\user\domain\model\Role;
use norsk\api\user\domain\valueObjects\UserName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * AuthorizationDecision wurde durch AuthorizationResult (Domain) ersetzt.
 * Dieser Test verbleibt als Orientierungsmarke.
 *
 * @see AuthorizationResultTest für die vollständigen Tests der Nachfolgerklasse.
 */
#[CoversClass(AuthorizationResult::class)]
class AuthorizationDecisionTest extends TestCase
{
    public function testDeniedResultEquality(): void
    {
        self::assertEquals(
            AuthorizationResult::denied(),
            AuthorizationResult::denied()
        );
    }


    public function testGrantedResultHasCorrectValues(): void
    {
        $userName = UserName::by('someUser');
        $role = Role::MANAGER;

        $result = AuthorizationResult::granted($userName, $role);

        self::assertTrue($result->isAuthorized());
        self::assertSame($userName, $result->getUserName());
        self::assertSame($role, $result->getRole());
    }
}
