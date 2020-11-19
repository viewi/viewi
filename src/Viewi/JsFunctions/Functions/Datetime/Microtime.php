<?php

namespace Viewi\JsFunctions\Functions\Datetime;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Microtime extends BaseFunctionConverter
{
    public static string $name = 'microtime';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Microtime.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
