<?php

declare(strict_types=1);

namespace norsk\api\helperTools;

use norsk\api\app\config\File;
use norsk\api\app\config\Path;

trait Removable
{
    public function removeLog(Path $logPath): void
    {
        $cleaner = new DirectoryCleaner();
        $cleaner->deleteAllFilesInDirectoryOf(File::fromPath($logPath));
    }
}
