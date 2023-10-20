<?php

namespace Viewi\Builder\Attributes;

use Attribute;

/**
 * Class marked with this attribute will not be included in JS bundle
 * @package Viewi\Builder\Attributes
 */
#[Attribute(Attribute::TARGET_CLASS)]
class CustomJs
{
}
