<?php

namespace Viewi\Builder;

use Viewi\JsTranspile\JsOutput;
use Viewi\JsTranspile\UseItem;

class BuildItem
{
    public bool $Ready = false;
    public ?array $Extends = null;
    public ?string $Namespace = null;
    public ?string $TemplatePath = null;
    /**
     * 
     * @var array<string, UseItem>> 
     */
    public array $Uses = [];

    public function __construct(public string $ComponentName, public JsOutput $JsOutput, public bool $Include = false)
    {
    }
}
