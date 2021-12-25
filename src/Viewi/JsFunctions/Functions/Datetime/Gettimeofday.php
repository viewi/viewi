<?php

namespace Viewi\JsFunctions\Functions\Datetime;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Gettimeofday extends BaseFunctionConverter
{
    public static string $name = 'gettimeofday';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Gettimeofday.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
