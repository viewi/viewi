<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Acosh extends BaseFunctionConverter
{
    public static string $name = 'acosh';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Acosh.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
