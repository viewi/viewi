<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class ArrayDiff extends BaseFunctionConverter
{
    public static string $name = 'array_diff';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayDiff.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
