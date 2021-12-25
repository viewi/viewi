<?php

namespace Viewi\JsFunctions\Functions\Pcre;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class PregReplace extends BaseFunctionConverter
{
    public static string $name = 'preg_replace';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $indentation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'PregReplace.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
