<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Decbin extends BaseFunctionConverter
{
    public static string $name = 'decbin';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Decbin.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
