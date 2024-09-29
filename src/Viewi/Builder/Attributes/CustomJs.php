<?php

namespace Viewi\Builder\Attributes;

use Attribute;

/**
 * Class marked with this attribute will have custom JavaScript
 * @package Viewi\Builder\Attributes
 */
#[Attribute(Attribute::TARGET_CLASS)]
class CustomJs
{
    /**
     * @param bool $export True (default) if you want to include the code automatically 
     * @return void 
     */
    public function __construct(public bool $export = true) {}
}
