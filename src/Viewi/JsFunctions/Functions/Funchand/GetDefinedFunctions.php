<?php

namespace Viewi\JsFunctions\Functions\Funchand;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class GetDefinedFunctions extends BaseFunctionConverter
{
    public static string $name = 'get_defined_functions';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'GetDefinedFunctions.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
