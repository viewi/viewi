<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Ceil extends BaseFunctionConverter
{
    public static string $name = 'ceil';

    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclude = __DIR__ . DIRECTORY_SEPARATOR . 'Ceil.js';
        $translator->includeJsFile(self::$name, $jsToInclude);
        return $code . '(';
    }
}
