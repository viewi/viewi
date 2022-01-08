<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Bindec extends BaseFunctionConverter
{
    public static string $name = 'bindec';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Bindec.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
