<?php

namespace Viewi\JsFunctions\Functions\Vars;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class IsInteger extends BaseFunctionConverter
{
    public static string $name = 'is_integer';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('is_int');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'IsInteger.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
