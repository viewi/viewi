<?php

namespace Viewi\JsFunctions\Functions\Netgopher;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class GopherParsedir extends BaseFunctionConverter
{
    public static string $name = 'gopher_parsedir';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'GopherParsedir.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
