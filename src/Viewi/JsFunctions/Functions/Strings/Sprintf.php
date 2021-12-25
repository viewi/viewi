<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Sprintf extends BaseFunctionConverter
{
    public static string $name = 'sprintf';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Sprintf.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
