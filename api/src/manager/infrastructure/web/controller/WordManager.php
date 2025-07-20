<?php

declare(strict_types=1);

namespace norsk\api\manager\infrastructure\web\controller;

use GuzzleHttp\Psr7\ServerRequest;
use norsk\api\infrastructure\logging\Logger;
use norsk\api\infrastructure\logging\LogMessage;
use norsk\api\infrastructure\routing\ControllerInterface;
use norsk\api\manager\application\wordManaging\useCases\CreateWord;
use norsk\api\manager\application\wordManaging\useCases\DeleteWord;
use norsk\api\manager\application\wordManaging\useCases\GetAllWords;
use norsk\api\manager\application\wordManaging\useCases\UpdateWord;
use norsk\api\manager\application\wordManaging\WordCreator;
use norsk\api\manager\application\wordManaging\WordRemover;
use norsk\api\manager\application\wordManaging\WordsProvider;
use norsk\api\manager\application\wordManaging\WordUpdater;
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

class WordManager implements ControllerInterface
{
    public function __construct(
        private readonly Logger $logger,
        private readonly WordsProvider $wordsProvider,
        private readonly WordCreator $wordCreator,
        private readonly WordUpdater $wordUpdater,
        private readonly WordRemover $wordRemover,
        private readonly Url $url
    ) {
    }


    public function getAllWords(AuthenticatedUserInterface $user): ResponseInterface
    {
        try {
            $userName = $user->getUserName();
            $command = GetAllWords::create();
            $allWords = $this->wordsProvider->handle($command);
            $json = $allWords->asJson();

            $this->logger->info(
                LogMessage::fromString(
                    'Generated list of Words: ' . $json->asString()
                    . ' by manager: ' . $userName->asString()
                )
            );

            return VocabularyListResponse::create($this->url, $json);
        } catch (Throwable $throwable) {
            $this->logger->error($throwable);

            return ErrorResponse::serverError($this->url, $throwable);
        }
    }


    public function createWord(AuthenticatedUserInterface $user, ServerRequest $request): ResponseInterface
    {
        try {
            $payload = Payload::of($request);
            $command = CreateWord::createBy($payload);
            $this->wordCreator->handle($command);

            $this->logger->info(
                LogMessage::fromString(
                    'Created Word: ' . $payload->asJson()->asString()
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

            $command = UpdateWord::createBy($id, $payload);
            $this->wordUpdater->handle($command);

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

            $command = DeleteWord::createBy($id);
            $this->wordRemover->handle($command);

            $idString = $command->getId()->asString();
            $json = Json::fromString('{"message":"Removed word with id: ' . $idString . '"}');
            $this->logger->info(LogMessage::fromString('Removed Id: ' . $idString));

            return SuccessResponse::deletedRecord($this->url, $json);
        } catch (Throwable $throwable) {
            $this->logger->error($throwable);

            return ManagerExceptionMapper::mapForDelete($throwable, $this->url);
        }
    }
}
