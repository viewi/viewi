<?php

namespace Viewi\Builder;

use Viewi\JsTranspile\JsOutput;
use Viewi\JsTranspile\UseItem;
use Viewi\TemplateCompiler\RenderItem;

class BuildItem
{
    public bool $Ready = false;
    public ?array $Extends = null;
    public ?string $Namespace = null;
    public ?string $TemplatePath = null;
    /**
     * 
     * @var array<string, string> see ExportItem
     */
    public array $publicNodes = [];
    /**
     * 
     * @var array<string, UseItem>> 
     */
    public array $Uses = [];
    public ?RenderItem $RenderFunction = null;

    public function __construct(public string $ComponentName, public JsOutput $JsOutput, public bool $Include = false)
    {
    }
}
