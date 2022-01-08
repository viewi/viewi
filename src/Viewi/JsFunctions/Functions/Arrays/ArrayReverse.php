<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class ArrayReverse extends BaseFunctionConverter
{
    public static string $name = 'array_reverse';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayReverse.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
