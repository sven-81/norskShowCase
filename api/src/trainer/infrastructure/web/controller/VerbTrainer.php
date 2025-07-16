<?php

declare(strict_types=1);

namespace norsk\api\trainer\infrastructure\web\controller;

use norsk\api\infrastructure\logging\Logger;
use norsk\api\infrastructure\logging\LogMessage;
use norsk\api\infrastructure\routing\ControllerInterface;
use norsk\api\shared\domain\Id;
use norsk\api\shared\infrastructure\http\response\exceptionMapper\TrainerExceptionMapper;
use norsk\api\shared\infrastructure\http\response\responses\ErrorResponse;
use norsk\api\shared\infrastructure\http\response\responses\NoContentResponse;
use norsk\api\shared\infrastructure\http\response\Url;
use norsk\api\trainer\application\verbTraining\useCases\GetVerbToTrain;
use norsk\api\trainer\application\verbTraining\useCases\SaveTrainedVerb;
use norsk\api\trainer\application\verbTraining\VerbProgressUpdater;
use norsk\api\trainer\application\verbTraining\VerbToTrainProvider;
use norsk\api\trainer\infrastructure\web\responses\VocabularyToTrainResponse;
use norsk\api\user\application\AuthenticatedUserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class VerbTrainer implements ControllerInterface
{
    public function __construct(
        private readonly Logger $logger,
        private readonly VerbToTrainProvider $getVerbToTrainHandler,
        private readonly VerbProgressUpdater $saveTrainedVerbHandler,
        private readonly Url $url,
    ) {
    }


    public function getVerbToTrain(AuthenticatedUserInterface $user): ResponseInterface
    {
        try {
            $userName = $user->getUserName();
            $command = GetVerbToTrain::for($userName);
            $verbToTrain = $this->getVerbToTrainHandler->handle($command);

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


    public function saveSuccess(AuthenticatedUserInterface $user, ServerRequestInterface $request): ResponseInterface
    {
        try {
            $userName = $user->getUserName();
            $id = Id::fromString($request->getAttribute('id'));

            $command = SaveTrainedVerb::for($userName, $id);
            $this->saveTrainedVerbHandler->handle($command);

            $this->logger->info(
                LogMessage::fromString(
                    'Saved successfully trained verbId: ' . $id->asString()
                    . ' for user: ' . $userName->asString()
                )
            );

            return NoContentResponse::vocabularyTrainedSuccessfully($this->url);
        } catch (Throwable $throwable) {
            $this->logger->error($throwable);

            return TrainerExceptionMapper::map($throwable, $this->url);
        }
    }
}
