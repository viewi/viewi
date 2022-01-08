<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Sin extends BaseFunctionConverter
{
    public static string $name = 'sin';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Sin.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
