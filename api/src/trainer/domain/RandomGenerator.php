<?php

declare(strict_types=1);

namespace norsk\api\trainer\domain;

use LogicException;
use norsk\api\shared\domain\Id;
use norsk\api\shared\domain\TrainingVocabulary;
use norsk\api\shared\domain\Vocabularies;
use norsk\api\trainer\domain\verbs\TrainingVerb;
use norsk\api\trainer\domain\words\TrainingWord;
use OutOfBoundsException;

class RandomGenerator
{
    private const int PER_CENT = 100;


    public function __construct(private readonly RandomNumber $randomNumber)
    {
    }


    public function pickFrom(Vocabularies $allVocabulariesForUser): TrainingWord|TrainingVerb
    {
        $possibilities = $this->calculatePossibilities($allVocabulariesForUser);
        $pickedVocabularyId = $this->pickARandomIdBasedOnPossibilityWeight($possibilities);

        return $allVocabulariesForUser->pick(Id::by($pickedVocabularyId));
    }


    private function calculatePossibilities(Vocabularies $allVocabulariesForUser): array
    {
        $possibilities = [];

        /** @var TrainingWord|TrainingVerb $vocabulary */
        foreach ($allVocabulariesForUser as $vocabulary) {
            $this->ensureIsNotManaging($vocabulary);
            $successCounter = $vocabulary->getSuccessCounter();
            $vocabularyId = $vocabulary->getId()->asInt();
            if ($successCounter->isInitial()) {
                $possibilities = $this->unratedVocabulariesHaveHighestPossibilityWeight($possibilities, $vocabularyId);
            } else {
                $possibilities = $this->reverseWeightForPossibilities($successCounter, $possibilities, $vocabularyId);
            }
        }

        return $this->normalizePossibilitiesInPerCent($possibilities);
    }


    private function ensureIsNotManaging(TrainingVocabulary $vocabulary): void
    {
        if (!$vocabulary instanceof TrainingWord && !$vocabulary instanceof TrainingVerb) {
            throw new LogicException('Vocabulary type has to be TrainingWord or TrainingVerb');
        }
    }


    private function unratedVocabulariesHaveHighestPossibilityWeight(array $possibilities, int $vocabularyId): array
    {
        $possibilities[$vocabularyId] = 1;

        return $possibilities;
    }


    private function reverseWeightForPossibilities(
        SuccessCounter $successCounter,
        array $possibilities,
        int $vocabularyId
    ): array {
        $possibilities[$vocabularyId] = 1 / $successCounter->asInt();

        return $possibilities;
    }


    private function normalizePossibilitiesInPerCent(array $possibilities): array
    {
        $totalWeight = array_sum($possibilities);

        foreach ($possibilities as $key => $possibility) {
            $possibilities[$key] = $possibility / $totalWeight * self::PER_CENT;
        }

        return $possibilities;
    }


    private function pickARandomIdBasedOnPossibilityWeight(array $possibilities): int
    {
        $randomInt = $this->randomNumber->asInt();
        $counter = 0;

        foreach ($possibilities as $vocabularyId => $possibility) {
            $counter += $possibility;
            if ($randomInt <= $counter) {
                return $vocabularyId;
            }
        }

        throw new OutOfBoundsException('No Vocabulary can be chosen randomly for RandomNumber: ' . $randomInt);
    }
}
