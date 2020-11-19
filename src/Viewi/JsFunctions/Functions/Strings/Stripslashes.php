<?php

namespace Viewi\JsFunctions\Functions\Strings;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Stripslashes extends BaseFunctionConverter
{
    public static string $name = 'stripslashes';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Stripslashes.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
