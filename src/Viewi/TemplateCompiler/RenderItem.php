<?php

namespace Viewi\TemplateCompiler;

use Viewi\TemplateParser\TagItem;

class RenderItem
{
    /**
     * 
     * @param string $renderCode 
     * @param bool $empty 
     * @param string $renderName 
     * @param array<array<string|TagItem|RenderItem>> $slots 
     * @param array<string, bool> $usedFunctions 
     * @return void 
     */
    public function __construct(
        public string $renderCode,
        public bool $empty,
        public string $renderName,
        public array $slots,
        public array $usedFunctions,
        public array $inlineExpressions
    ) {
    }

    public function __toString()
    {
        return $this->renderCode;
    }

    public function generatePhpContent(): string
    {
        $code = '<?php' . PHP_EOL . $this->renderCode;
        foreach ($this->slots as $slotTuple) {
            $code .= $slotTuple[1]->renderCode;
        }
        return $code;
    }
}
