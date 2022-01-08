<?php

namespace Viewi\JsFunctions\Functions\Funchand;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class CallUserFunc extends BaseFunctionConverter
{
    public static string $name = 'call_user_func';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('call_user_func_array');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'CallUserFunc.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
