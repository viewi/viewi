<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Deg2rad extends BaseFunctionConverter
{
    public static string $name = 'deg2rad';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Deg2rad.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
