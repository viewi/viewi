<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Arsort extends BaseFunctionConverter
{
    public static string $name = 'arsort';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Arsort.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
