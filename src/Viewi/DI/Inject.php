<?php

namespace Viewi\DI;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class Inject
{
    public const NAME = 'Inject';

    public function __construct(public string $scope)
    {
    }
}
