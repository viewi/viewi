<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class ArrayChangeKeyCase extends BaseFunctionConverter
{
    public static string $name = 'array_change_key_case';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayChangeKeyCase.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
