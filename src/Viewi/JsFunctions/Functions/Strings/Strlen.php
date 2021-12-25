<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Strlen extends BaseFunctionConverter
{
    public static string $name = 'strlen';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Strlen.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
