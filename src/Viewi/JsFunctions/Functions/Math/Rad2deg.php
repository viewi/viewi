<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Rad2deg extends BaseFunctionConverter
{
    public static string $name = 'rad2deg';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Rad2deg.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
