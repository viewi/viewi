<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Hexdec extends BaseFunctionConverter
{
    public static string $name = 'hexdec';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Hexdec.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
