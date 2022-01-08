<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class ArrayIntersectKey extends BaseFunctionConverter
{
    public static string $name = 'array_intersect_key';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayIntersectKey.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
