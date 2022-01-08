<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Log10 extends BaseFunctionConverter
{
    public static string $name = 'log10';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Log10.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
