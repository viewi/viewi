<?php

namespace Viewi;

class ViewiPath
{
    public static function dir(): string
    {
        return __DIR__;
    }

    public static function viewiJsDir(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'js';
    }

    public static function viewiJsPackageDir(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'exports';
    }

    public static function viewiJsPackageName(): string
    {
        return 'viewi';
    }

    public static function viewiJsCoreDir(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'viewi';
    }
}
