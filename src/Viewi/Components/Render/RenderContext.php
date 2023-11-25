<?php

namespace Viewi\Components\Render;

use Viewi\Builder\Attributes\Skip;

#[Skip]
class RenderContext
{
    public function __construct(public string $component, public array $props, public array $slots, public array $scope, public array $params = [])
    {
    }
}
