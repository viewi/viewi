<?php

namespace Viewi\TemplateCompiler;

use Viewi\Builder\BuildItem;
use Viewi\JsTranspile\JsTranspiler;
use Viewi\Meta\Meta;
use Viewi\TemplateParser\TagItem;

class TemplateCompiler
{
    private string $code;
    private string $template;

    public function __construct(private JsTranspiler $jsTranspiler)
    {
    }

    public function compile(TagItem $rootTag, BuildItem $buildItem, $templateKey = ''): string
    {
        $this->reset();
        $renderFunctionTemplate = $this->template ?? ($this->template = file_get_contents(Meta::renderFunctionPath()));
        $parts = explode("//#content", $renderFunctionTemplate, 2);
        $this->code .= $parts[0];
        $renderFunction = "Render{$buildItem->ComponentName}$templateKey";
        $this->code = str_replace('BaseComponent $', ($buildItem->Namespace ?? '') . '\\' . $buildItem->ComponentName . ' $', $this->code);
        $this->code = str_replace('RenderFunction', $renderFunction, $this->code);



        $this->code .= $parts[1];
        return $this->code;
    }

    private function reset()
    {
        $this->code = '';
    }
}
