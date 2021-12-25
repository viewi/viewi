<?php

namespace Viewi\JsFunctions\Functions\Vars;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Gettype extends BaseFunctionConverter
{
    public static string $name = 'gettype';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Gettype.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
