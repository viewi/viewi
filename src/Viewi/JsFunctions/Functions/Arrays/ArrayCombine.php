<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class ArrayCombine extends BaseFunctionConverter
{
    public static string $name = 'array_combine';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayCombine.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
