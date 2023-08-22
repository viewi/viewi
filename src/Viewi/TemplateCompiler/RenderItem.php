<?php

namespace Viewi\TemplateCompiler;

class RenderItem
{
    /**
     * 
     * @param string $renderCode 
     * @param bool $empty 
     * @param string $renderName 
     * @param array<string|RenderItem[]> $slots 
     * @param array<string, bool> $usedFunctions 
     * @return void 
     */
    public function __construct(
        public string $renderCode,
        public bool $empty,
        public string $renderName,
        public array $slots,
        public array $usedFunctions
    ) {
    }

    public function __toString()
    {
        return $this->renderCode;
    }
}
