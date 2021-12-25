<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Htmlentities extends BaseFunctionConverter
{
    public static string $name = 'htmlentities';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Htmlentities.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
