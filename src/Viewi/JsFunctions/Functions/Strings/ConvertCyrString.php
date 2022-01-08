<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class ConvertCyrString extends BaseFunctionConverter
{
    public static string $name = 'convert_cyr_string';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'ConvertCyrString.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
