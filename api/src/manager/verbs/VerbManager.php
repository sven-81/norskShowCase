<?php

declare(strict_types=1);

namespace norsk\api\manager\verbs;

use Exception;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use norsk\api\app\identityAccessManagement\Session;
use norsk\api\app\logging\Logger;
use norsk\api\app\logging\LogMessage;
use norsk\api\app\request\Payload;
use norsk\api\app\response\ResponseCode;
use norsk\api\app\response\Url;
use norsk\api\manager\ManagerWriter;
use norsk\api\manager\responses\VocabularyListResponse;
use norsk\api\shared\Id;
use norsk\api\shared\Json;
use norsk\api\shared\responses\CreatedResponse;
use norsk\api\shared\responses\ErrorResponse;
use norsk\api\shared\responses\NoContentResponse;
use norsk\api\shared\responses\SuccessResponse;
use norsk\api\shared\VocabularyType;
use Psr\Http\Message\ResponseInterface;
use Slim\Routing\RouteContext;
use Throwable;

class VerbManager
{
    private readonly VocabularyType $vocabularyType;


    public function __construct(
        private readonly Logger $logger,
        private readonly VerbReader $verbReader,
        private readonly ManagerWriter $managerWriter,
        private readonly Url $url
    ) {
        $this->vocabularyType = VocabularyType::verb;
    }


    public function getAllVerbs(): ResponseInterface
    {
        try {
            $userName = Session::getUserName();
            $words = $this->verbReader->getAllVerbs();
            $json = $words->asJson();
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


    public function createVerb(ServerRequest $request): ResponseInterface
    {
        try {
            $payload = Payload::of($request);
            $this->verbReader->ensureVerbsAreNotAlreadyPersisted(null, $payload);
            $this->managerWriter->add($payload, $this->vocabularyType);

            return CreatedResponse::savedVocabulary($this->url);
        } catch (Throwable $throwable) {
            $this->logger->error($throwable);

            return match ($throwable->getCode()) {
                ResponseCode::conflict->value => $this->entryAlreadyExistsResponse($throwable),
                default => ErrorResponse::serverError($this->url, $throwable),
            };
        }
    }


    private function entryAlreadyExistsResponse(Throwable|Exception $throwable): Response
    {
        return ErrorResponse::conflict($this->url, $throwable);
    }


    public function update(ServerRequest $request): ResponseInterface
    {
        try {
            $routeContext = RouteContext::fromRequest($request);
            $route = $routeContext->getRoute();
            $idString = $route->getArgument('id');
            $id = Id::fromString($idString);
            $payload = Payload::of($request);

            $this->verbReader->ensureVerbsAreNotAlreadyPersisted($id, $payload);
            $this->managerWriter->update($id, $payload, $this->vocabularyType);
            $this->logger->info(
                LogMessage::fromString(
                    'Updated Id: ' . $id->asString()
                    . ' to: ' . $payload->asJson()->asString()
                )
            );

            return NoContentResponse::updatedVocabularySuccessfully($this->url);
        } catch (Throwable $throwable) {
            $this->logger->error($throwable);

            return match ($throwable->getCode()) {
                ResponseCode::notFound->value => $this->noVerbFoundForRequestedId($throwable),
                ResponseCode::conflict->value => $this->entryAlreadyExistsResponse($throwable),
                default => ErrorResponse::serverError($this->url, $throwable),
            };
        }
    }


    private function noVerbFoundForRequestedId(Throwable|Exception $throwable): Response
    {
        return ErrorResponse::notFound($this->url, $throwable);
    }


    public function delete(ServerRequest $request): ResponseInterface
    {
        try {
            $routeContext = RouteContext::fromRequest($request);
            $route = $routeContext->getRoute();
            $idString = $route->getArgument('id');

            $id = Id::fromString($idString);
            $this->managerWriter->remove($id, $this->vocabularyType);

            $json = Json::fromString('{"message":"Removed verb with id: ' . $id->asString() . '"}');
            $this->logger->info(LogMessage::fromString('Removed Id: ' . $id->asString()));

            return SuccessResponse::deletedRecord($this->url, $json);
        } catch (Throwable $throwable) {
            $this->logger->error($throwable);

            return match ($throwable->getCode()) {
                ResponseCode::notFound->value => $this->noVerbFoundForRequestedId($throwable),
                default => ErrorResponse::serverError($this->url, $throwable),
            };
        }
    }
}
