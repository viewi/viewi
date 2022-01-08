<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Cos extends BaseFunctionConverter
{
    public static string $name = 'cos';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Cos.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
