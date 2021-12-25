<?php

namespace Viewi\JsFunctions\Functions\Datetime;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Gmstrftime extends BaseFunctionConverter
{
    public static string $name = 'gmstrftime';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Gmstrftime.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
