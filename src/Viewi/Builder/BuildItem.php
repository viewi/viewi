<?php

namespace Viewi\Builder;

use Viewi\JsTranspile\JsOutput;
use Viewi\JsTranspile\UseItem;
use Viewi\TemplateCompiler\RenderItem;
use Viewi\TemplateParser\TagItem;

class BuildItem
{
    public bool $Ready = false;
    public ?array $Extends = null;
    public ?array $Attributes = null;
    public bool $Skip = false;
    public ?string $Namespace = null;
    public ?string $TemplatePath = null;
    public bool $HtmlRootComponentCalculated = false;
    public ?string $HtmlRootComponent = null;
    /**
     * 
     * @var array<string, string> see ExportItem
     */
    public array $publicNodes = [];
    /**
     * 
     * @var array<string, bool>
     */
    public array $refs = [];
    /**
     * 
     * @var array<string, UseItem>> 
     */
    public array $Uses = [];
    public ?RenderItem $RenderFunction = null;
    public ?TagItem $RootTag = null;

    public function __construct(public string $ComponentName, public JsOutput $JsOutput, public bool $Include = false)
    {
    }
}
