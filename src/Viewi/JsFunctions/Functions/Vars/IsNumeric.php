<?php

namespace Viewi\JsFunctions\Functions\Vars;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class IsNumeric extends BaseFunctionConverter
{
    public static string $name = 'is_numeric';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'IsNumeric.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
