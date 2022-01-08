<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Getrandmax extends BaseFunctionConverter
{
    public static string $name = 'getrandmax';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Getrandmax.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
