<?php

namespace Viewi\JsFunctions\Functions\Info;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Getenv extends BaseFunctionConverter
{
    public static string $name = 'getenv';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Getenv.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
