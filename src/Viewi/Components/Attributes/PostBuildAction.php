<?php

namespace Viewi\Components\Attributes;

use Attribute;
use Viewi\Builder\Attributes\Skip;

/**
 * Post build action
 */
#[Skip]
#[Attribute(Attribute::TARGET_CLASS)]
class PostBuildAction
{
    public function __construct(public string $className)
    {
    }
}
