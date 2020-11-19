<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Vsprintf extends BaseFunctionConverter
{
    public static string $name = 'vsprintf';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Vsprintf.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
