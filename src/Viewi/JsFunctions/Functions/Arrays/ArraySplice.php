<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class ArraySplice extends BaseFunctionConverter
{
    public static string $name = 'array_splice';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'ArraySplice.js';
        $translator->includeFunction('is_int');
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
