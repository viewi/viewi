<?php

namespace Viewi\Packages;

abstract class ViewiPackage
{
    abstract static function getComponentsPath(): ?string;
    abstract static function jsDir(): ?string;
    abstract static function jsModulePackagePath(): ?string;
    abstract static function name(): string;
    abstract static function assetsPath(): ?string;
    /**
     * 
     * @return ViewiPackage[] 
     */
    abstract static function getDependencies(): array;
}
