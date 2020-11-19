<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class StrRot13 extends BaseFunctionConverter
{
    public static string $name = 'str_rot13';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'StrRot13.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
