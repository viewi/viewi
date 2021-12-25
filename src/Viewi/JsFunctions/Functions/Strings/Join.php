<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Join extends BaseFunctionConverter
{
    public static string $name = 'join';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Join.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
