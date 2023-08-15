<?php

namespace Viewi\Meta;

class Meta
{
    public static function dir(): string
    {
        return __DIR__;
    }

    public static function renderFunctionPath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'RenderFunction.php';
    }
}
