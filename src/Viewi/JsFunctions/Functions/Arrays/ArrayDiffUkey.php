<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class ArrayDiffUkey extends BaseFunctionConverter
{
    public static string $name = 'array_diff_ukey';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayDiffUkey.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
