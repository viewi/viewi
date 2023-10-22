<?php

namespace Viewi\PhpJsFunctions\Funchand;

use Viewi\JsTranspile\BaseFunction;

class CallUserFuncArray extends BaseFunction
{
    public static string $name = 'call_user_func_array';

    public static function getUses(): array
    {
        return [];
    }

    public static function getJs(): string
    {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'CallUserFuncArray.js';
        return file_get_contents($jsToInclude);
    }
}
