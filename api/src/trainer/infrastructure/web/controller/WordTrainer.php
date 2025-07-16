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
use norsk\api\trainer\application\wordTraining\useCases\GetWordToTrain;
use norsk\api\trainer\application\wordTraining\useCases\SaveTrainedWord;
use norsk\api\trainer\application\wordTraining\WordProgressUpdater;
use norsk\api\trainer\application\wordTraining\WordToTrainProvider;
use norsk\api\trainer\infrastructure\web\responses\VocabularyToTrainResponse;
use norsk\api\user\application\AuthenticatedUserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class WordTrainer implements ControllerInterface
{
    public function __construct(
        private readonly Logger $logger,
        private readonly WordToTrainProvider $getWordToTrainHandler,
        private readonly WordProgressUpdater $saveTrainedWordHandler,
        private readonly Url $url,
    ) {
    }


    public function getWordToTrain(AuthenticatedUserInterface $user): ResponseInterface
    {
        try {
            $userName = $user->getUserName();
            $command = GetWordToTrain::for($userName);
            $wordToTrain = $this->getWordToTrainHandler->handle($command);

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


    public function saveSuccess(AuthenticatedUserInterface $user, ServerRequestInterface $request): ResponseInterface
    {
        try {
            $userName = $user->getUserName();
            $id = Id::fromString($request->getAttribute('id'));

            $command = SaveTrainedWord::for($userName, $id);
            $this->saveTrainedWordHandler->handle($command);

            $this->logger->info(
                LogMessage::fromString(
                    'Saved successfully trained wordId: ' . $id->asString()
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
