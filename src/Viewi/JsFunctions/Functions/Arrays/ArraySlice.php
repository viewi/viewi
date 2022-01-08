<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class ArraySlice extends BaseFunctionConverter
{
    public static string $name = 'array_slice';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('is_int');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArraySlice.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
