<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class BaseConvert extends BaseFunctionConverter
{
    public static string $name = 'base_convert';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'BaseConvert.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
