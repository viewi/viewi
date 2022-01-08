<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Max extends BaseFunctionConverter
{
    public static string $name = 'max';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Max.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
