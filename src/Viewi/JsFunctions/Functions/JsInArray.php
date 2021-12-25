<?php

namespace Viewi\JsFunctions\Functions;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class JsInArray extends BaseFunctionConverter
{
    public static string $name = 'in_array';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'JsInArray.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
