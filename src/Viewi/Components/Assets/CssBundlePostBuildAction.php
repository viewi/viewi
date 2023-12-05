<?php

namespace Viewi\Components\Assets;

use Viewi\Builder\Attributes\Skip;
use Viewi\Builder\Builder;
use Viewi\Components\PostBuild\IPostBuildAction;

#[Skip]
class CssBundlePostBuildAction implements IPostBuildAction
{
    public function build(Builder $builder, array $props)
    {
        print_r($props);
    }
}
