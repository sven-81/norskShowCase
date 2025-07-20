<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\routing;

use norsk\api\user\infrastructure\identityAccessManagement\authentication\RouteHandlerWithAuthenticatedUser;
use norsk\api\user\infrastructure\identityAccessManagement\IdentityAccessManagementFactory;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

class Router
{
    public function __construct(
        private readonly IdentityAccessManagementFactory $identityAccessManagement,
        private readonly Context $context,
        private readonly ControllerResolver $controllerResolver,
    ) {
    }


    public function run(App $app): void
    {
        $app->group('/api/v1', function (RouteCollectorProxy $group): void {
            $this->defineApiRoutes($group);
        }
        )->addMiddleware($this->identityAccessManagement->createAuthentication());
    }


    private function defineApiRoutes(RouteCollectorProxy $group): void
    {
        $this->routeUsers($group);
        $this->defineTrainRoutes($group);
        $this->defineManageRoutes($group);
    }


    private function defineTrainRoutes(RouteCollectorProxy $group): void
    {
        $group->group(
            pattern: '/train',
            callable: function (RouteCollectorProxy $trainGroup): void {
                $this->trainWords($trainGroup);
                $this->trainVerbs($trainGroup);
            }
        )->addMiddleware($this->identityAccessManagement->createTrainerAuthorization());
    }


    private function defineManageRoutes(RouteCollectorProxy $group): void
    {
        $group->group(
            pattern: '/manage',
            callable: function (RouteCollectorProxy $manageGroup): void {
                $this->manageWords($manageGroup);
                $this->manageVerbs($manageGroup);
            }
        )->addMiddleware($this->identityAccessManagement->createManagerAuthorization());
    }


    private function routeUsers(RouteCollectorProxy $group): void
    {
        $group->post(
            pattern: '/user/new',
            callable: [$this->context->userManagement()->registration(), 'registerUser',]
        );
        $group->post(
            pattern: '/user',
            callable: [$this->context->userManagement()->login(), 'run']
        );
    }


    private function trainWords(RouteCollectorProxy $group): void
    {
        $group->get(
            pattern: '/words',
            callable: RouteHandlerWithAuthenticatedUser::by(
                $this->controllerResolver,
                ControllerName::of($this->context->trainer()->wordTrainer()),
                Method::of('getWordToTrain')
            )
        );
        $group->patch(
            pattern: '/words/{id:[0-9a-zA-Z]+}',
            callable: RouteHandlerWithAuthenticatedUser::by(
                $this->controllerResolver,
                ControllerName::of($this->context->trainer()->wordTrainer()),
                Method::of('saveSuccess')
            )
        );
    }


    private function trainVerbs(RouteCollectorProxy $group): void
    {
        $group->get(
            pattern: '/verbs',
            callable: RouteHandlerWithAuthenticatedUser::by(
                $this->controllerResolver,
                ControllerName::of($this->context->trainer()->verbTrainer()),
                Method::of('getVerbToTrain')
            )
        );
        $group->patch(
            pattern: '/verbs/{id:[0-9a-zA-Z]+}',
            callable: RouteHandlerWithAuthenticatedUser::by(
                $this->controllerResolver,
                ControllerName::of($this->context->trainer()->verbTrainer()),
                Method::of('saveSuccess')
            )
        );
    }


    private function manageWords(RouteCollectorProxy $group): void
    {
        $group->get(
            pattern: '/words',
            callable: RouteHandlerWithAuthenticatedUser::by(
                $this->controllerResolver,
                ControllerName::of($this->context->manager()->wordManager()),
                Method::of('getAllWords')
            )
        );
        $group->post(
            pattern: '/words',
            callable: RouteHandlerWithAuthenticatedUser::by(
                $this->controllerResolver,
                ControllerName::of($this->context->manager()->wordManager()),
                Method::of('createWord')
            )
        );
        $group->put(
            pattern: '/words/{id:[0-9]+}',
            callable: RouteHandlerWithAuthenticatedUser::by(
                $this->controllerResolver,
                ControllerName::of($this->context->manager()->wordManager()),
                Method::of('update')
            )
        );
        $group->delete(
            pattern: '/words/{id:[0-9]+}',
            callable: RouteHandlerWithAuthenticatedUser::by(
                $this->controllerResolver,
                ControllerName::of($this->context->manager()->wordManager()),
                Method::of('delete')
            )
        );
    }


    private function manageVerbs(RouteCollectorProxy $group): void
    {
        $group->get(
            pattern: '/verbs',
            callable: RouteHandlerWithAuthenticatedUser::by(
                $this->controllerResolver,
                ControllerName::of($this->context->manager()->verbManager()),
                Method::of('getAllVerbs')
            )
        );
        $group->post(
            pattern: '/verbs',
            callable: RouteHandlerWithAuthenticatedUser::by(
                $this->controllerResolver,
                ControllerName::of($this->context->manager()->verbManager()),
                Method::of('createVerb')
            )
        );
        $group->put(
            pattern: '/verbs/{id:[0-9]+}',
            callable: RouteHandlerWithAuthenticatedUser::by(
                $this->controllerResolver,
                ControllerName::of($this->context->manager()->verbManager()),
                Method::of('update')
            )
        );
        $group->delete(
            pattern: '/verbs/{id:[0-9]+}',
            callable: RouteHandlerWithAuthenticatedUser::by(
                $this->controllerResolver,
                ControllerName::of($this->context->manager()->verbManager()),
                Method::of('delete')
            )
        );
    }
}
