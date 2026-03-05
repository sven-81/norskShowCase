<?php

declare(strict_types=1);

use Arkitect\ClassSet;
use Arkitect\CLI\Config;
use Arkitect\Expression\ForClasses\HaveNameMatching;
use Arkitect\Expression\ForClasses\Implement;
use Arkitect\Expression\ForClasses\NotDependsOnTheseNamespaces;
use Arkitect\Expression\ForClasses\ResideInOneOfTheseNamespaces;
use Arkitect\Rules\Rule;
use norsk\api\user\application\AuthenticatedUserInterface;

return static function (Config $config): void {
    $classSet = ClassSet::fromDir(__DIR__ . '/../api/src');

    $r1 = Rule::allClasses()
        ->that(
            new ResideInOneOfTheseNamespaces(
                'norsk\api\manager\domain\exceptions',
                'norsk\api\shared\domain\exceptions',
                'norsk\api\trainer\domain\exceptions',
                'norsk\api\user\domain\exceptions'
            )
        )
        ->should(new HaveNameMatching('*Exception'))
        ->because('we want uniform naming');

    $r2 = Rule::allClasses()
        ->that(
            new ResideInOneOfTheseNamespaces(
                'norsk\api\manager\infrastructure\persistence\queries',
                'norsk\api\user\infrastructure\persistence\queries',
                'norsk\api\trainer\infrastructure\persistence\queries'
            )
        )
        ->should(new HaveNameMatching('*Sql'))
        ->because('we want uniform naming');

    $r3 = Rule::allClasses()
        ->that(new HaveNameMatching('JwtAuthenticatedUser'))
        ->should(new Implement(AuthenticatedUserInterface::class))
        ->because('we want wrap implementation');

    $r4 = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('api\manager\infrastructure\web\controller'))
        ->should(new NotDependsOnTheseNamespaces(['api\manager\infrastructure\persistence']))
        ->because('Controllers should use application-services, not persistence directly');

    $r5 = Rule::allClasses()
        ->that(new ResideInOneOfTheseNamespaces('api\manager\domain'))
        ->should(new NotDependsOnTheseNamespaces(['api\manager\infrastructure']))
        ->because('Domain layer must not depend on infrastructure layer');

    $config->add($classSet, $r1, $r2, $r3, $r4, $r5);
};
