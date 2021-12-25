<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Substr extends BaseFunctionConverter
{
    public static string $name = 'substr';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Substr.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
