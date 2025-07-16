<?php

declare(strict_types=1);

namespace norsk\api\helperTools;

use norsk\api\infrastructure\config\File;
use norsk\api\infrastructure\config\Path;

trait Removable
{
    public function removeLog(Path $logPath): void
    {
        $cleaner = new DirectoryCleaner();
        $cleaner->deleteAllFilesInDirectoryOf(File::fromPath($logPath));
    }
}
