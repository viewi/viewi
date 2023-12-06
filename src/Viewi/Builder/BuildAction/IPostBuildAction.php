<?php

namespace Viewi\Builder\BuildAction;

use Viewi\Builder\Builder;

interface IPostBuildAction
{
    function build(Builder $builder, array $props): ?BuildActionItem;
}
