<?php

declare(strict_types=1);

namespace norsk\api\app\config;

use RuntimeException;

class File
{
    private function __construct(private readonly Path $path)
    {
    }


    public static function fromPath(Path $path): self
    {
        return new self($path);
    }


    public function parseIniFile(): IniItems
    {
        $this->isReadable();
        $content = parse_ini_file($this->getPath()->asString(), true, INI_SCANNER_TYPED);

        return IniItems::fromAssocArray($content);
    }


    private function isReadable(): void
    {
        if (!is_readable($this->path->asString())) {
            throw new RuntimeException("Cannot read file: {$this->path->asString()}");
        }
    }


    public function getPath(): Path
    {
        return $this->path;
    }
}
