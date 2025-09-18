<?php

namespace Viewi\Components\Callbacks;

use Exception;
use Throwable;
use Viewi\Builder\Attributes\Skip;

#[Skip]
class ResolverError extends Exception
{
    public function __construct(public $data = null) {}
}
