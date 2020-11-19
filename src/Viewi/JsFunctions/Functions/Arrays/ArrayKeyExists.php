<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class ArrayKeyExists extends BaseFunctionConverter
{
    public static string $name = 'array_key_exists';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'ArrayKeyExists.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
