<?php

namespace Viewi\JsFunctions\Functions\Arrays;

use Viewi\JsFunctions\BaseFunctionConverter;
use Viewi\JsTranslator;

class Current extends BaseFunctionConverter
{
    public static string $name = 'current';
    
    public static function convert(
        JsTranslator $translator,
        string $code,
        string $identation
    ): string {
        $jsToInclue = __DIR__ . DIRECTORY_SEPARATOR . 'Current.js';
        $translator->includeJsFile(self::$name, $jsToInclue);
        return $code . '(';
    }
}
