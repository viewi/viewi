<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Sqrt extends BaseFunctionConverter
{
    public static string $name = 'sqrt';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Sqrt.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
