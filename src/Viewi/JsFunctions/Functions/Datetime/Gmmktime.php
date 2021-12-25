<?php

namespace Viewi\JsFunctions\Functions\Datetime;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Gmmktime extends BaseFunctionConverter
{
    public static string $name = 'gmmktime';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Gmmktime.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
