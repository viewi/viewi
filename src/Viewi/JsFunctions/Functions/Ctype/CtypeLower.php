<?php

namespace Viewi\JsFunctions\Functions\Ctype;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class CtypeLower extends BaseFunctionConverter
{
    public static string $name = 'ctype_lower';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('setlocale');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'CtypeLower.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
