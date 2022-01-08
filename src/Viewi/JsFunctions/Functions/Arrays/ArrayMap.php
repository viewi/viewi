<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class ArrayMap extends BaseFunctionConverter
{
    public static string $name = 'array_map';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayMap.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
