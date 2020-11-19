<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Sinh extends BaseFunctionConverter
{
    public static string $name = 'sinh';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Sinh.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
