<?php

declare(strict_types=1);

namespace norsk\api\app\logging;

use norsk\api\app\config\Path;

readonly class AppLoggerConfig
{
    private function __construct(
        private Path $path,
        private bool $displayErrorDetails,
        private bool $logErrors,
        private bool $logErrorDetails
    ) {
    }


    public static function by(
        Path $path,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): self {
        return new self($path, $displayErrorDetails, $logErrors, $logErrorDetails);
    }


    public function getPath(): Path
    {
        return $this->path;
    }


    public function isDisplayErrorDetails(): bool
    {
        return $this->displayErrorDetails;
    }


    public function isLogErrors(): bool
    {
        return $this->logErrors;
    }


    public function isLogErrorDetails(): bool
    {
        return $this->logErrorDetails;
    }
}
