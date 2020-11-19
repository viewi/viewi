<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Strtolower extends BaseFunctionConverter
{
    public static string $name = 'strtolower';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Strtolower.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
