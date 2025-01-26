<?php

namespace Viewi\Components\Attributes;

use Attribute;
use Viewi\Builder\Attributes\Skip;

/**
 * Override component
 */
#[Skip]
#[Attribute(Attribute::TARGET_CLASS)]
class OverrideComponent
{
    public function __construct(public string $component) {}
}
