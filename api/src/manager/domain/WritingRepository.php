<?php

declare(strict_types=1);

namespace norsk\api\manager\domain;

use norsk\api\shared\domain\Id;
use norsk\api\shared\domain\ManagingVocabulary;
use norsk\api\shared\domain\VocabularyType;

interface WritingRepository
{
    public function add(ManagingVocabulary $vocabulary): void;


    public function update(ManagingVocabulary $vocabulary): void;


    public function remove(Id $id, VocabularyType $vocabularyType): void;
}
