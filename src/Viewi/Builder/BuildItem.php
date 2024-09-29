<?php

namespace Viewi\Builder;

use ReflectionClass;
use Viewi\JsTranspile\JsOutput;
use Viewi\JsTranspile\UseItem;
use Viewi\Packages\ViewiPackage;
use Viewi\TemplateCompiler\RenderItem;
use Viewi\TemplateParser\TagItem;

class BuildItem
{
    public bool $Ready = false;
    public ?array $Extends = null;
    public ?array $Attributes = null;
    public bool $Skip = false;
    public bool $CustomJs = false;
    public bool $NoJs = false;
    public bool $LazyLoad = false;
    public ?string $LazyLoadName = null;
    public bool $StartUp = false;
    public ?string $Namespace = null;
    public string $RelativePath = '';
    public int $RelativePathDepth = 0;
    public string $RelativeLookupPath = '';
    public ?string $TemplatePath = null;
    public ?string $CustomJsPath = null;
    public bool $CustomTs = false;
    public bool $HtmlRootComponentCalculated = false;
    public ?string $HtmlRootComponent = null;
    public ReflectionClass $ReflectionClass;
    public array $Props;
    public array $Methods;
    public array $DiProps = [];
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
    /**
     * @var null|ViewiPackage
     */
    public ?string $Package = null;

    public function __construct(public string $ComponentName, public JsOutput $JsOutput, public bool $Include = false)
    {
    }
}
