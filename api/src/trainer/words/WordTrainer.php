<?php

declare(strict_types=1);

namespace norsk\api\trainer\words;

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

class WordTrainer
{
    private readonly VocabularyType $vocabularyType;


    public function __construct(
        private readonly Logger $logger,
        private readonly RandomGenerator $randomGenerator,
        private readonly WordReader $wordReader,
        private readonly TrainingWriter $trainingWriter,
        private readonly Url $url,
    ) {
        $this->vocabularyType = VocabularyType::word;
    }


    public function getWordToTrain(): ResponseInterface
    {
        try {
            $userName = Session::getUserName();
            $allWordsForUser = $this->wordReader->getAllWordsFor($userName);
            $wordToTrain = $this->randomGenerator->pickFrom($allWordsForUser);
            $this->logger->info(
                LogMessage::fromString(
                    'Word to train: ' . $wordToTrain->asJson()->asString()
                    . ' for user: ' . $userName->asString()
                )
            );

            return VocabularyToTrainResponse::create($this->url, $wordToTrain->asJson());
        } catch (Throwable $throwable) {
            $this->logger->error($throwable);

            return ErrorResponse::serverError($this->url, $throwable);
        }
    }


    public function saveSuccess(ServerRequest $request): ResponseInterface
    {
        try {
            $userName = Session::getUserName();
            $id = $this->getWordIdFrom($request);

            $this->trainingWriter->save($userName, $id, $this->vocabularyType);
            $this->logger->info(
                LogMessage::fromString(
                    'Saved successfully trained wordId: ' . $id->asString()
                    . ' for user: ' . $userName->asString()
                )
            );

            return NoContentResponse::vocabularyTrainedSuccessfully($this->url);
        } catch (Throwable $throwable) {
            $this->logger->error($throwable);

            return match ($throwable->getCode()) {
                ResponseCode::badRequest->value => $this->parameterMissingResponse($throwable),
                ResponseCode::notFound->value => $this->wordIdNotFoundResponse($throwable),
                default => ErrorResponse::serverError($this->url, $throwable),
            };
        }
    }


    private function getWordIdFrom(ServerRequest $request): Id
    {
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();

        return Id::fromString($route->getArgument('id'));
    }


    private function parameterMissingResponse(Throwable|Exception $throwable): Response
    {
        return ErrorResponse::badRequest($this->url, $throwable);
    }


    private function wordIdNotFoundResponse(Throwable|Exception $throwable): Response
    {
        return ErrorResponse::notFound($this->url, $throwable);
    }
}
