<?php

namespace Viewi\TemplateCompiler;

class RenderItem
{
    /**
     * 
     * @param string $renderCode 
     * @param array<string|RenderItem[]> $slots 
     * @return void 
     */
    public function __construct(public string $renderCode, public string $renderName, public array $slots)
    {
    }

    public function __toString()
    {
        return $this->renderCode;
    }
}
