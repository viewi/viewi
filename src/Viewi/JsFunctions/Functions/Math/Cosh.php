<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Cosh extends BaseFunctionConverter
{
    public static string $name = 'cosh';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Cosh.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
