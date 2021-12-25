<?php

namespace Viewi\JsFunctions\Functions\Vars;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class IsObject extends BaseFunctionConverter
{
    public static string $name = 'is_object';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'IsObject.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
