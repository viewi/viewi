<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Tan extends BaseFunctionConverter
{
    public static string $name = 'tan';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Tan.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
