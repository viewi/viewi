<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Atan extends BaseFunctionConverter
{
    public static string $name = 'atan';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Atan.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
