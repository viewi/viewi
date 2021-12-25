<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Exp extends BaseFunctionConverter
{
    public static string $name = 'exp';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Exp.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
