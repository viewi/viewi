<?php

namespace Viewi\Packages;

use Viewi\PageEngine;

abstract class ViewiPackage
{
    abstract function getComponentsPath(): ?string;

    abstract function getAssetsPath(): ?string;

    abstract function onBuild(PageEngine $pageEngine): void;
}
