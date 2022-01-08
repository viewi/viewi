<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class StrIreplace extends BaseFunctionConverter
{
    public static string $name = 'str_ireplace';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'StrIreplace.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
