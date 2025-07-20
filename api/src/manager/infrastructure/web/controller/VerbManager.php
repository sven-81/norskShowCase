<?php

declare(strict_types=1);

namespace norsk\api\manager\infrastructure\web\controller;

use GuzzleHttp\Psr7\ServerRequest;
use norsk\api\infrastructure\logging\Logger;
use norsk\api\infrastructure\logging\LogMessage;
use norsk\api\infrastructure\routing\ControllerInterface;
use norsk\api\manager\application\verbManaging\useCases\CreateVerb;
use norsk\api\manager\application\verbManaging\useCases\DeleteVerb;
use norsk\api\manager\application\verbManaging\useCases\GetAllVerbs;
use norsk\api\manager\application\verbManaging\useCases\UpdateVerb;
use norsk\api\manager\application\verbManaging\VerbCreator;
use norsk\api\manager\application\verbManaging\VerbRemover;
use norsk\api\manager\application\verbManaging\VerbsProvider;
use norsk\api\manager\application\verbManaging\VerbUpdater;
use norsk\api\manager\infrastructure\web\responses\VocabularyListResponse;
use norsk\api\shared\application\Json;
use norsk\api\shared\domain\Id;
use norsk\api\shared\infrastructure\http\request\Payload;
use norsk\api\shared\infrastructure\http\response\exceptionMapper\ManagerExceptionMapper;
use norsk\api\shared\infrastructure\http\response\responses\CreatedResponse;
use norsk\api\shared\infrastructure\http\response\responses\ErrorResponse;
use norsk\api\shared\infrastructure\http\response\responses\NoContentResponse;
use norsk\api\shared\infrastructure\http\response\responses\SuccessResponse;
use norsk\api\shared\infrastructure\http\response\Url;
use norsk\api\user\application\AuthenticatedUserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class VerbManager implements ControllerInterface
{
    public function __construct(
        private readonly Logger $logger,
        private readonly VerbsProvider $verbsProvider,
        private readonly VerbCreator $verbCreator,
        private readonly VerbUpdater $verbUpdater,
        private readonly VerbRemover $verbRemover,
        private readonly Url $url
    ) {
    }


    public function getAllVerbs(AuthenticatedUserInterface $user): ResponseInterface
    {
        try {
            $userName = $user->getUserName();
            $command = GetAllVerbs::create();
            $allVerbs = $this->verbsProvider->handle($command);
            $json = $allVerbs->asJson();

            $this->logger->info(
                LogMessage::fromString(
                    'Generated list of Verbs: ' . $json->asString()
                    . ' by manager: ' . $userName->asString()
                )
            );

            return VocabularyListResponse::create($this->url, $json);
        } catch (Throwable $throwable) {
            $this->logger->error($throwable);

            return ErrorResponse::serverError($this->url, $throwable);
        }
    }


    public function createVerb(AuthenticatedUserInterface $user, ServerRequest $request): ResponseInterface
    {
        try {
            $payload = Payload::of($request);
            $command = CreateVerb::createBy($payload);
            $this->verbCreator->handle($command);

            $this->logger->info(
                LogMessage::fromString(
                    'Created Verb: ' . $payload->asJson()->asString()
                )
            );

            return CreatedResponse::savedVocabulary($this->url);
        } catch (Throwable $throwable) {
            $this->logger->error($throwable);

            return ManagerExceptionMapper::mapForCreate($throwable, $this->url);
        }
    }


    public function update(AuthenticatedUserInterface $user, ServerRequestInterface $request): ResponseInterface
    {
        try {
            $id = Id::fromString($request->getAttribute('id'));
            $payload = Payload::of($request);

            $command = UpdateVerb::createBy($id, $payload);
            $this->verbUpdater->handle($command);

            $this->logger->info(
                LogMessage::fromString(
                    'Updated Id: ' . $id->asString()
                    . ' to: ' . $payload->asJson()->asString()
                )
            );

            return NoContentResponse::updatedVocabularySuccessfully($this->url);
        } catch (Throwable $throwable) {
            $this->logger->error($throwable);

            return ManagerExceptionMapper::mapForUpdate($throwable, $this->url);
        }
    }


    public function delete(AuthenticatedUserInterface $user, ServerRequest $request): ResponseInterface
    {
        try {
            $id = Id::fromString($request->getAttribute('id'));

            $command = DeleteVerb::createBy($id);
            $this->verbRemover->handle($command);

            $json = Json::fromString('{"message":"Removed verb with id: ' . $id->asString() . '"}');
            $this->logger->info(LogMessage::fromString('Removed Id: ' . $id->asString()));

            return SuccessResponse::deletedRecord($this->url, $json);
        } catch (Throwable $throwable) {
            $this->logger->error($throwable);

            return ManagerExceptionMapper::mapForDelete($throwable, $this->url);
        }
    }
}
