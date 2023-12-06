<?php

namespace Viewi\Builder\BuildAction;

class BuildActionItem
{
    public function __construct(public string $type, public $data = null, public ?array $publicConfig = null)
    {
    }
}
