<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Hypot extends BaseFunctionConverter
{
    public static string $name = 'hypot';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Hypot.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
