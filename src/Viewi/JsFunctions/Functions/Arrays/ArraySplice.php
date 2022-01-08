<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class ArraySplice extends BaseFunctionConverter
{
    public static string $name = 'array_splice';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('is_int');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArraySplice.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
