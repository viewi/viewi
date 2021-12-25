<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Log extends BaseFunctionConverter
{
    public static string $name = 'log';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Log.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
