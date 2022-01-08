<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class ArrayColumn extends BaseFunctionConverter
{
    public static string $name = 'array_column';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayColumn.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
