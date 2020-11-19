<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class StrPad extends BaseFunctionConverter
{
    public static string $name = 'str_pad';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'StrPad.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
