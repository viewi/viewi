<?php

namespace Viewi\JsFunctions\Functions\Ctype;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class CtypeDigit extends BaseFunctionConverter
{
    public static string $name = 'ctype_digit';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $translator->includeFunction('setlocale');
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'CtypeDigit.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
