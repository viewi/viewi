<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Ucwords extends BaseFunctionConverter
{
    public static string $name = 'ucwords';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Ucwords.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
