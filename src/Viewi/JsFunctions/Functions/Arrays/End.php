<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class End extends BaseFunctionConverter
{
    public static string $name = 'end';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'End.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
