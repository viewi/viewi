<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class IsInfinite extends BaseFunctionConverter
{
    public static string $name = 'is_infinite';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'IsInfinite.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
