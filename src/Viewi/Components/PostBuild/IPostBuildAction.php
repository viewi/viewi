<?php

namespace Viewi\Components\PostBuild;

use Viewi\Builder\Builder;

interface IPostBuildAction
{
    function build(Builder $builder, array $props);
}
