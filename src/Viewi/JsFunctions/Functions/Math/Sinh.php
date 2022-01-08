<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Sinh extends BaseFunctionConverter
{
    public static string $name = 'sinh';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Sinh.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
