<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class StrReplace extends BaseFunctionConverter
{
    public static string $name = 'str_replace';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'StrReplace.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
