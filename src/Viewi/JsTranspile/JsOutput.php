<?php

namespace Viewi\JsTranspile;

class JsOutput
{
    public function __construct(private string $jsCode, private array $exports = [], private array $uses = [], private array $varDeps = [])
    {
    }

    /**
     * 
     * @return array<string, ExportItem>
     */
    public function getExports(): array
    {
        return $this->exports;
    }
    /**
     * 
     * @return array<string, UseItem>> 
     */
    public function getUses(): array
    {
        return $this->uses;
    }

    public function getVariableDependencies(): array
    {
        return $this->uses;
    }

    public function __toString(): string
    {
        return $this->jsCode;
    }
}
