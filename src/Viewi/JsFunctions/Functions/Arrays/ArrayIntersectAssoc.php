<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class ArrayIntersectAssoc extends BaseFunctionConverter
{
    public static string $name = 'array_intersect_assoc';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayIntersectAssoc.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
