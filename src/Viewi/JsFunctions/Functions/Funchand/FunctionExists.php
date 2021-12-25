<?php

namespace Viewi\JsFunctions\Functions\Funchand;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class FunctionExists extends BaseFunctionConverter
{
    public static string $name = 'function_exists';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'FunctionExists.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
