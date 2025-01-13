<?php

namespace Viewi\Components;

use Viewi\Builder\Attributes\Skip;
use Viewi\Packages\ViewiPackage;

#[Skip]
class ViewiCorePackage extends ViewiPackage
{
    public static function getComponentsPath(): array
    {
        return [__DIR__];
    }

    public static function jsDir(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'js';
    }

    public static function jsModulePackagePath(): string
    {
        return 'viewi';
    }

    public static function name(): string
    {
        return 'viewi';
    }

    public static function assetsPath(): ?string
    {
        return null;
    }

    public static function getDependencies(): array
    {
        return [];
    }
}
