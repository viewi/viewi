<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class IsNan extends BaseFunctionConverter
{
    public static string $name = 'is_nan';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'IsNan.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
