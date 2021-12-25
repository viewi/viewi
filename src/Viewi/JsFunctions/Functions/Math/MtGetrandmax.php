<?php

namespace Viewi\JsFunctions\Functions\Math;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class MtGetrandmax extends BaseFunctionConverter
{
    public static string $name = 'mt_getrandmax';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'MtGetrandmax.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
