<?php

namespace Viewi\JsFunctions\Functions\Json;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class JsonDecode extends BaseFunctionConverter
{
    public static string $name = 'json_decode';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'JsonDecode.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
