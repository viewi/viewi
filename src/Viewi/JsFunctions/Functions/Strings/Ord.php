<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Ord extends BaseFunctionConverter
{
    public static string $name = 'ord';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Ord.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
