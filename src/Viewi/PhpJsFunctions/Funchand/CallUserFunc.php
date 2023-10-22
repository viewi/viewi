<?php

namespace Viewi\PhpJsFunctions\Funchand;

use Viewi\JsTranspile\BaseFunction;

class CallUserFunc extends BaseFunction
{
    public static string $name = 'call_user_func';

    public static function getUses(): array
    {
        return ['call_user_func_array'];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'CallUserFunc.js';
        return file_get_contents($jsToInclude);
    }
}
