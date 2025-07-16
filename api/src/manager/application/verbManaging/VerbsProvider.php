<?php

declare(strict_types=1);

namespace norsk\api\manager\application\verbManaging;

use norsk\api\manager\application\verbManaging\useCases\GetAllVerbs;
use norsk\api\shared\domain\Vocabularies;
use norsk\api\trainer\domain\verbs\ManagingVerbReadingRepository;

readonly class VerbsProvider
{
    public function __construct(
        private ManagingVerbReadingRepository $verbReader,
    ) {
    }


    public function handle(GetAllVerbs $command): Vocabularies
    {
        return $this->verbReader->getAllVerbs();
    }
}