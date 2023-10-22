<?php

namespace Viewi\Components\Attributes;

use Attribute;
use Viewi\Builder\Attributes\Skip;

/**
 * Preserve property value for reuse on front-end side
 */
#[Skip]
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class Preserve
{
}
