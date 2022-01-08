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
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayCombine.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
