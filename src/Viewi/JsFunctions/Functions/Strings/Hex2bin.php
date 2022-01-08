<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Hex2bin extends BaseFunctionConverter
{
    public static string $name = 'hex2bin';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Hex2bin.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
