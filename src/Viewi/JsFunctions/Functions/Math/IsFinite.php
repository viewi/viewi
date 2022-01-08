<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class IsFinite extends BaseFunctionConverter
{
    public static string $name = 'is_finite';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'IsFinite.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
