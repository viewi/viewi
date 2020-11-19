<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Strrpos extends BaseFunctionConverter
{
    public static string $name = 'strrpos';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Strrpos.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
