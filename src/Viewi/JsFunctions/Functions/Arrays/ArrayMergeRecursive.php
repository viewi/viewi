<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class ArrayMergeRecursive extends BaseFunctionConverter
{
    public static string $name = 'array_merge_recursive';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('array_merge');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayMergeRecursive.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
