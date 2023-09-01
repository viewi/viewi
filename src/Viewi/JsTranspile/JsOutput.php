<?php

namespace Viewi\JsTranspile;

class JsOutput
{
    public function __construct(
        private string $jsCode,
        private array $exports = [],
        private array $uses = [],
        private array $varDeps = [],
        private array $transforms = []
    ) {
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

    public function getTransforms(): array
    {
        return $this->transforms;
    }

    public function getDeps(): array
    {
        return $this->varDeps;
    }

    public function __toString(): string
    {
        return $this->jsCode;
    }
}
