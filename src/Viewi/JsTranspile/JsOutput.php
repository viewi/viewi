<?php

namespace Viewi\JsTranspile;

class JsOutput
{
    public function __construct(private string $jsCode, private array $exports = [])
    {
    }

    public function getExports(): array
    {
        return $this->exports;
    }

    public function __toString(): string
    {
        return $this->jsCode;
    }
}
