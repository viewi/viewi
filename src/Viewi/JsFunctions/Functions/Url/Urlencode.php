<?php

namespace Viewi\JsFunctions\Functions\Url;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Urlencode extends BaseFunctionConverter
{
    public static string $name = 'urlencode';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Urlencode.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
