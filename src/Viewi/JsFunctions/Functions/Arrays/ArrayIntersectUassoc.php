<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class ArrayIntersectUassoc extends BaseFunctionConverter
{
    public static string $name = 'array_intersect_uassoc';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayIntersectUassoc.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
