<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class ArrayKeys extends BaseFunctionConverter
{
    public static string $name = 'array_keys';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayKeys.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
