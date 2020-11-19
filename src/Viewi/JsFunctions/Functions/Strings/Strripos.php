<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Strripos extends BaseFunctionConverter
{
    public static string $name = 'strripos';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Strripos.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
