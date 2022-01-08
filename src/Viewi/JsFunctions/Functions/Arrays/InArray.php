<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class InArray extends BaseFunctionConverter
{
    public static string $name = 'in_array';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'InArray.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
