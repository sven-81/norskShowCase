<?php

declare(strict_types=1);

namespace norsk\api\infrastructure\routing;

use InvalidArgumentException;

readonly class ControllerName
{
    private string $class;


    private function __construct(
        ControllerInterface $controller
    ) {
        $this->class = get_class($controller);
        $this->ensureClassExists();
    }


    public static function of(ControllerInterface $controller): self
    {
        return new self($controller);
    }


    public function asString(): string
    {
        return $this->class;
    }


    private function ensureClassExists(): void
    {
        // @codeCoverageIgnoreStart
        if (!class_exists($this->class)) {
            throw new InvalidArgumentException('Controller class does not exist: ' . $this->class);
        }
        // @codeCoverageIgnoreEnd
    }
}
