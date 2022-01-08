<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Acos extends BaseFunctionConverter
{
    public static string $name = 'acos';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Acos.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
