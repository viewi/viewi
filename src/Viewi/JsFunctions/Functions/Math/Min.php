<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Min extends BaseFunctionConverter
{
    public static string $name = 'min';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Min.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
