<?php

declare(strict_types=1);

namespace norsk\api\manager\domain;

use norsk\api\shared\application\Json;
use norsk\api\shared\domain\ManagingVocabulary;
use norsk\api\tests\provider\WordProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ManagedVocabularies::class)]
class ManagedVocabulariesTest extends TestCase
{
    public function testCreatesEmptyCollection(): void
    {
        $vocabularies = ManagedVocabularies::create();

        self::assertCount(0, iterator_to_array($vocabularies->getIterator()));
    }


    public function testCanAddVocabulary(): void
    {
        $vocabularies = ManagedVocabularies::create();
        $vocabularies->add(WordProvider::managedWordArchipelago());

        self::assertCount(1, iterator_to_array($vocabularies->getIterator()));
    }


    public function testCanAddMultipleVocabularies(): void
    {
        $vocabularies = ManagedVocabularies::create();
        $vocabularies->add(WordProvider::managedWordArchipelago());
        $vocabularies->add(WordProvider::managedWordArchipelago());

        self::assertCount(2, iterator_to_array($vocabularies->getIterator()));
    }


    public function testCanIterateOverVocabularies(): void
    {
        $vocabularies = ManagedVocabularies::create();
        $vocabularies->add(WordProvider::managedWordArchipelago());

        foreach ($vocabularies as $vocabulary) {
            self::assertInstanceOf(ManagingVocabulary::class, $vocabulary);
        }
    }


    public function testAsJsonReturnsEncodedCollection(): void
    {
        $vocabularies = ManagedVocabularies::create();
        $vocabularies->add(WordProvider::managedWordArchipelago());

        $json = $vocabularies->asJson();

        self::assertInstanceOf(Json::class, $json);
        self::assertSame('[' . WordProvider::managedWordArchipelagoAsJsonString() . ']', $json->asString());
    }


    public function testAsJsonReturnsEmptyArrayForEmptyCollection(): void
    {
        $vocabularies = ManagedVocabularies::create();

        self::assertSame('[]', $vocabularies->asJson()->asString());
    }
}
