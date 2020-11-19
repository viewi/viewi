<?php

namespace Viewi\JsFunctions\Functions\Vars;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class IsBuffer extends BaseFunctionConverter
{
    public static string $name = 'is_buffer';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'IsBuffer.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
