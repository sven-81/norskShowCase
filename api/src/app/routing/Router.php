<?php

declare(strict_types=1);

namespace norsk\api\app\routing;

use norsk\api\app\identityAccessManagement\IdentityAccessManagementFactory;
use norsk\api\app\identityAccessManagement\Session;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

class Router
{
    public function __construct(
        private readonly IdentityAccessManagementFactory $identityAccessManagement,
        private readonly Context $context,
    ) {
    }


    public function run(App $app, Session $session): void
    {
        $app->group(
            pattern: '/api/v1',
            callable: function (RouteCollectorProxy $group): void {
                $this->routeUsers($group);
                $this->routeWordTraining($group);
                $this->routeVerbTraining($group);

                $group->group(
                    pattern: '/manage',
                    callable: function (RouteCollectorProxy $group): void {
                        $this->manageWords($group);
                        $this->manageVerbs($group);
                    }
                )->addMiddleware($this->identityAccessManagement->createAuthorization());
            }
        )->addMiddleware($this->identityAccessManagement->createAuthentication($session));
    }


    private function routeUsers(RouteCollectorProxy $group): void
    {
        $group->post(
            pattern: '/user/new',
            callable: [$this->context->registration(), 'registerUser',]
        );
        $group->post(
            pattern: '/user',
            callable: [$this->context->login(), 'run']
        );
    }


    private function routeWordTraining(RouteCollectorProxy $group): void
    {
        $group->get(
            pattern: '/train/words',
            callable: [$this->context->wordTrainer(), 'getWordToTrain',]
        );
        $group->patch(
            pattern: '/train/words/{id:[0-9a-zA-Z]+}',
            callable: [$this->context->wordTrainer(), 'saveSuccess']
        );
    }


    private function routeVerbTraining(RouteCollectorProxy $group): void
    {
        $group->get(
            pattern: '/train/verbs',
            callable: [$this->context->verbTrainer(), 'getVerbToTrain',]
        );
        $group->patch(
            pattern: '/train/verbs/{id:[0-9a-zA-Z]+}',
            callable: [$this->context->verbTrainer(), 'saveSuccess']
        );
    }


    private function manageWords(RouteCollectorProxy $group): void
    {
        $group->get(
            pattern: '/words',
            callable: [$this->context->wordManager(), 'getAllWords']
        );
        $group->post(
            pattern: '/words',
            callable: [$this->context->wordManager(), 'createWord']
        );
        $group->put(
            pattern: '/words/{id:[0-9]+}',
            callable: [$this->context->wordManager(), 'update']
        );
        $group->delete(
            pattern: '/words/{id:[0-9]+}',
            callable: [$this->context->wordManager(), 'delete']
        );
    }


    private function manageVerbs(RouteCollectorProxy $group): void
    {
        $group->get(
            pattern: '/verbs',
            callable: [$this->context->verbManager(), 'getAllVerbs']
        );
        $group->post(
            pattern: '/verbs',
            callable: [$this->context->verbManager(), 'createVerb']
        );
        $group->put(
            pattern: '/verbs/{id:[0-9]+}',
            callable: [$this->context->verbManager(), 'update']
        );
        $group->delete(
            pattern: '/verbs/{id:[0-9]+}',
            callable: [$this->context->verbManager(), 'delete']
        );
    }
}
