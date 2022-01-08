<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Soundex extends BaseFunctionConverter
{
    public static string $name = 'soundex';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Soundex.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
