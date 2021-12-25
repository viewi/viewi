<?php

namespace Viewi\JsFunctions\Functions\Vars;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class IsBool extends BaseFunctionConverter
{
    public static string $name = 'is_bool';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'IsBool.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
