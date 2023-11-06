<?php

namespace Viewi\Components\Attributes;

use Attribute;
use Viewi\Builder\Attributes\Skip;

/**
 * Lazy load component on front-end
 */
#[Skip]
#[Attribute(Attribute::TARGET_CLASS)]
class LazyLoad
{
    public function __construct(public string $groupName = '')
    {
    }
}
