<?php

declare(strict_types=1);

namespace norsk\api\helperTools;

use norsk\api\infrastructure\config\File;
use norsk\api\infrastructure\config\Path;

class DirectoryCleaner
{
    public function deleteAllFilesInDirectoryOf(File $sourcePath): void
    {
        $files = glob($sourcePath->getPath()->asString() . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }


    public function deleteDirectoryRecursiveOf(File $inputFile): void
    {
        if (file_exists($inputFile->getPath()->asString())) {
            $files = glob($inputFile->getPath()->asString() . '/*');
            foreach ($files as $file) {
                if (is_dir($file)) {
                    self::deleteDirectoryRecursiveOf(File::fromPath(Path::fromString($file)));
                } else {
                    unlink($file);
                }
            }

            rmdir($inputFile->getPath()->asString());
        }
    }


    public function deleteFileIfExists(File $file): void
    {
        if (file_exists($file->getPath()->asString())) {
            unlink($file->getPath()->asString());
        }
    }
}
