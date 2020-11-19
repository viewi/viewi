<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Range extends BaseFunctionConverter
{
    public static string $name = 'range';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Range.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
