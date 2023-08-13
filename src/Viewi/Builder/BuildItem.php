<?php

namespace Viewi\Builder;

use Viewi\JsTranspile\JsOutput;

class BuildItem
{
    public bool $Ready = false;
    public ?array $Extends = null;
    public ?string $Namespace = null;
    public ?string $TemplatePath = null;
    public array $Uses = [];

    public function __construct(public string $ComponentName, public JsOutput $JsOutput, public bool $Include = false)
    {
    }
}
