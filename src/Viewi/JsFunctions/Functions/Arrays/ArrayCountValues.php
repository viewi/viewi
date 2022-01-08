<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class ArrayCountValues extends BaseFunctionConverter
{
    public static string $name = 'array_count_values';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayCountValues.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
