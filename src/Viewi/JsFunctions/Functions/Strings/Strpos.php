<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Strpos extends BaseFunctionConverter
{
    public static string $name = 'strpos';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Strpos.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
