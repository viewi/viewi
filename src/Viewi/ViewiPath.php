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
}
