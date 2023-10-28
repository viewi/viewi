<?php

namespace Viewi\Components\Attributes;

use Attribute;
use Viewi\Builder\Attributes\Skip;

/**
 * Middleware (Guard) for component
 */
#[Skip]
#[Attribute(Attribute::TARGET_CLASS)]
class Middleware
{
    /**
     * 
     * @param string[] $middlewareList 
     * @return void 
     */
    public function __construct(public array $middlewareList)
    {
    }
}
