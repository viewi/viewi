<?php

namespace Viewi\JsFunctions\Functions\Url;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Base64Encode extends BaseFunctionConverter
{
    public static string $name = 'base64_encode';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Base64Encode.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
