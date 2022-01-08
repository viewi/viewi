<?php

namespace Viewi\JsFunctions\Functions\Pcre;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class PregMatch extends BaseFunctionConverter
{
    public static string $name = 'preg_match';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'PregMatch.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
