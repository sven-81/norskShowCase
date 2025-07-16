<?php

declare(strict_types=1);

namespace norsk\api\manager\infrastructure;

use norsk\api\infrastructure\config\AppConfig;
use norsk\api\infrastructure\logging\Logger;
use norsk\api\infrastructure\persistence\DbConnection;
use norsk\api\manager\application\verbManaging\VerbCreator;
use norsk\api\manager\application\verbManaging\VerbRemover;
use norsk\api\manager\application\verbManaging\VerbsProvider;
use norsk\api\manager\application\verbManaging\VerbUpdater;
use norsk\api\manager\application\wordManaging\WordCreator;
use norsk\api\manager\application\wordManaging\WordRemover;
use norsk\api\manager\application\wordManaging\WordsProvider;
use norsk\api\manager\application\wordManaging\WordUpdater;
use norsk\api\manager\infrastructure\persistence\ManagerWriter;
use norsk\api\manager\infrastructure\persistence\SqlUniquenessPolicy;
use norsk\api\manager\infrastructure\persistence\VerbReader;
use norsk\api\manager\infrastructure\persistence\WordReader;
use norsk\api\manager\infrastructure\web\controller\VerbManager;
use norsk\api\manager\infrastructure\web\controller\WordManager;
use norsk\api\shared\domain\VocabularyType;

class ManagerFactory
{
    public function __construct(
        private readonly Logger $logger,
        private readonly DbConnection $dbConnection,
        private readonly AppConfig $appConfig
    ) {
    }


    public function wordManager(): WordManager
    {
        $reader = new WordReader($this->dbConnection);
        $writer = new ManagerWriter($this->dbConnection);
        $policy = new SqlUniquenessPolicy($this->dbConnection, VocabularyType::word);

        return new WordManager(
            $this->logger,
            new WordsProvider($reader),
            new WordCreator($writer, $policy),
            new WordUpdater($writer, $policy),
            new WordRemover($writer),
            $this->appConfig->getUrl()
        );
    }


    public function verbManager(): VerbManager
    {
        $reader = new VerbReader($this->dbConnection);
        $writer = new ManagerWriter($this->dbConnection);
        $policy = new SqlUniquenessPolicy($this->dbConnection, VocabularyType::verb);

        return new VerbManager(
            $this->logger,
            new VerbsProvider($reader),
            new VerbCreator($writer, $policy),
            new VerbUpdater($writer, $policy),
            new VerbRemover($writer),
            $this->appConfig->getUrl()
        );
    }
}
