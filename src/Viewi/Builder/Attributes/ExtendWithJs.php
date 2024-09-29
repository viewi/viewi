<?php

namespace Viewi\Builder\Attributes;

use Attribute;

/**
 * Class marked with this attribute will have additional JavaScript implementation, partially PHP ,partially JS.
 * @package Viewi\Builder\Attributes
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ExtendWithJs
{
    /**
     * @return void 
     */
    public function __construct() {}
}
