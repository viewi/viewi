<?php

namespace Viewi\JsFunctions\Functions\Funchand;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class CallUserFuncArray extends BaseFunctionConverter
{
    public static string $name = 'call_user_func_array';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'CallUserFuncArray.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
