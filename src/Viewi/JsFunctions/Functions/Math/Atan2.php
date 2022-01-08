<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Atan2 extends BaseFunctionConverter
{
    public static string $name = 'atan2';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Atan2.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
