<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Stripos extends BaseFunctionConverter
{
    public static string $name = 'stripos';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Stripos.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
