<?php

namespace Viewi\JsFunctions\Functions\Url;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Base64Decode extends BaseFunctionConverter
{
    public static string $name = 'base64_decode';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Base64Decode.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
