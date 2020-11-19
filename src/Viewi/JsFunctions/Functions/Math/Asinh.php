<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Asinh extends BaseFunctionConverter
{
    public static string $name = 'asinh';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Asinh.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
