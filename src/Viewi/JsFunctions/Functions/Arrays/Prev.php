<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Prev extends BaseFunctionConverter
{
    public static string $name = 'prev';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Prev.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
