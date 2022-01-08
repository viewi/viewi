<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Abs extends BaseFunctionConverter
{
    public static string $name = 'abs';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Abs.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
