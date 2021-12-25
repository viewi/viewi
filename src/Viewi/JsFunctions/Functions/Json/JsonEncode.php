<?php

namespace Viewi\JsFunctions\Functions\Json;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class JsonEncode extends BaseFunctionConverter
{
    public static string $name = 'json_encode';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'JsonEncode.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
