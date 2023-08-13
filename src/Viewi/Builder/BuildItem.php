<?php

namespace Viewi\Builder;

use Viewi\JsTranspile\JsOutput;

class BuildItem
{
    public bool $Include = false;

    public function __construct(public string $ComponentName, public JsOutput $JsOutput)
    {
    }
}
