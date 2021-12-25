<?php

namespace Viewi\JsFunctions\Functions\Vars;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Unserialize extends BaseFunctionConverter
{
    public static string $name = 'unserialize';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Unserialize.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
