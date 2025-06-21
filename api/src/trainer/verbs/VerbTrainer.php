<?php

declare(strict_types=1);

namespace norsk\api\trainer\verbs;

use Exception;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use norsk\api\app\identityAccessManagement\Session;
use norsk\api\app\logging\Logger;
use norsk\api\app\logging\LogMessage;
use norsk\api\app\response\ResponseCode;
use norsk\api\app\response\Url;
use norsk\api\shared\Id;
use norsk\api\shared\responses\ErrorResponse;
use norsk\api\shared\responses\NoContentResponse;
use norsk\api\shared\VocabularyType;
use norsk\api\trainer\RandomGenerator;
use norsk\api\trainer\responses\VocabularyToTrainResponse;
use norsk\api\trainer\TrainingWriter;
use Psr\Http\Message\ResponseInterface;
use Slim\Routing\RouteContext;
use Throwable;

class VerbTrainer
{
    private readonly VocabularyType $vocabularyType;


    public function __construct(
        private readonly Logger $logger,
        private readonly RandomGenerator $randomGenerator,
        private readonly VerbReader $verbReader,
        private readonly TrainingWriter $trainingWriter,
        private readonly Url $url,
    ) {
        $this->vocabularyType = VocabularyType::verb;
    }


    public function getVerbToTrain(): ResponseInterface
    {
        try {
            $userName = Session::getUserName();
            $allVerbsForUser = $this->verbReader->getAllVerbsFor($userName);

            $verbToTrain = $this->randomGenerator->pickFrom($allVerbsForUser);
            $this->logger->info(
                LogMessage::fromString(
                    'Verb to train: ' . $verbToTrain->asJson()->asString()
                    . ' for user: ' . $userName->asString()
                )
            );

            return VocabularyToTrainResponse::create($this->url, $verbToTrain->asJson());
        } catch (Throwable $throwable) {
            $this->logger->error($throwable);

            return ErrorResponse::serverError($this->url, $throwable);
        }
    }


    public function saveSuccess(ServerRequest $request): ResponseInterface
    {
        try {
            $userName = Session::getUserName();
            $id = $this->getVerbIdFrom($request);

            $this->trainingWriter->save($userName, $id, $this->vocabularyType);
            $this->logger->info(
                LogMessage::fromString(
                    'Saved successfully trained verbId: ' . $id->asString()
                    . ' for user: ' . $userName->asString()
                )
            );

            return NoContentResponse::vocabularyTrainedSuccessfully($this->url);
        } catch (Throwable $throwable) {
            $this->logger->error($throwable);

            return match ($throwable->getCode()) {
                ResponseCode::badRequest->value => $this->parameterMissingResponse($throwable),
                ResponseCode::notFound->value => $this->verbIdNotFoundResponse($throwable),
                default => ErrorResponse::serverError($this->url, $throwable),
            };
        }
    }


    private function getVerbIdFrom(ServerRequest $request): Id
    {
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();

        return Id::fromString($route->getArgument('id'));
    }


    private function parameterMissingResponse(Throwable|Exception $throwable): Response
    {
        return ErrorResponse::badRequest($this->url, $throwable);
    }


    private function verbIdNotFoundResponse(Throwable|Exception $throwable): Response
    {
        return ErrorResponse::notFound($this->url, $throwable);
    }
}
