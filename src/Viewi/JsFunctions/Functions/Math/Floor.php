<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Floor extends BaseFunctionConverter
{
    public static string $name = 'floor';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Floor.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
